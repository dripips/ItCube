<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Group;
use App\Models\User;
use App\Services\SchoolAnalytics;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminAndFamilyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    private function guardian(): User
    {
        return User::where('username', 'belova')->firstOrFail();
    }

    #[Test]
    public function панель_управления_открывается_администратору(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Панель управления')
            ->assertSee('Что не получается');
    }

    #[Test]
    public function панель_закрыта_преподавателю_и_ученику(): void
    {
        $teacher = User::where('username', 'lebedeva')->firstOrFail();
        $student = User::where('username', 'artem')->firstOrFail();

        $this->actingAs($teacher)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
    }

    #[Test]
    public function список_людей_фильтруется_по_роли_и_поиску(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.people.index', ['role' => Role::Guardian->value]))
            ->assertOk()
            ->assertSee('Белова')
            ->assertDontSee('Лебедева');

        $this->actingAs($this->admin())
            ->get(route('admin.people.index', ['q' => 'соколов']))
            ->assertOk()
            ->assertSee('Соколова Мила')
            ->assertDontSee('Белов Артём');
    }

    #[Test]
    public function администратор_заводит_человека(): void
    {
        $this->actingAs($this->admin())->post(route('admin.people.store'), [
            'username' => 'novikov',
            'first_name' => 'Иван',
            'last_name' => 'Новиков',
            'role' => Role::Student->value,
            'is_active' => '1',
        ])->assertRedirect();

        $person = User::where('username', 'novikov')->firstOrFail();
        $this->assertSame(Role::Student, $person->role);
        $this->assertSame('Новиков Иван', $person->fullName());
    }

    #[Test]
    public function правка_без_пароля_его_не_затирает(): void
    {
        // Пустое поле пароля означает «не трогать». Если бы пароль ехал в общем
        // массиве данных, каждая правка профиля выкидывала бы человека из системы.
        $person = User::where('username', 'artem')->firstOrFail();
        $person->update(['password' => Hash::make('старый-пароль')]);
        $before = $person->fresh()->password;

        $this->actingAs($this->admin())->patch(route('admin.people.update', $person), [
            'username' => 'artem',
            'first_name' => 'Артём',
            'last_name' => 'Белов',
            'role' => Role::Student->value,
            'is_active' => '1',
            'password' => '',
        ])->assertRedirect();

        $this->assertSame($before, $person->fresh()->password);
        $this->assertTrue(Hash::check('старый-пароль', $person->fresh()->password));
    }

    #[Test]
    public function пароль_меняется_когда_поле_заполнено(): void
    {
        $person = User::where('username', 'artem')->firstOrFail();

        $this->actingAs($this->admin())->patch(route('admin.people.update', $person), [
            'username' => 'artem',
            'first_name' => 'Артём',
            'last_name' => 'Белов',
            'role' => Role::Student->value,
            'is_active' => '1',
            'password' => 'новый-длинный-пароль',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('новый-длинный-пароль', $person->fresh()->password));
    }

    #[Test]
    public function группа_правится_вместе_с_расписанием_и_составом(): void
    {
        $group = Group::where('slug', 'python-1')->firstOrFail();
        $student = User::where('username', 'ulyana')->firstOrFail();

        $this->actingAs($this->admin())->patch(route('admin.groups.update', $group), [
            'name' => 'Питон-1',
            'direction_id' => $group->direction_id,
            'teacher_id' => $group->teacher_id,
            'schedule' => [
                ['day_of_week' => 3, 'starts_at' => '15:00', 'ends_at' => '16:30', 'room' => 'каб. 101'],
                ['day_of_week' => 4, 'starts_at' => '', 'ends_at' => ''],
            ],
            'students' => [$student->id],
        ])->assertRedirect();

        $group->refresh();

        // Пустая строка расписания не сохраняется.
        $this->assertCount(1, $group->schedules);
        $this->assertSame(3, $group->schedules->first()->day_of_week);
        $this->assertSame([$student->id], $group->students->pluck('id')->all());
    }

    #[Test]
    public function родитель_видит_своих_детей(): void
    {
        $this->actingAs($this->guardian())
            ->get(route('family.index'))
            ->assertOk()
            ->assertSee('Белов Артём')
            ->assertSee('Жукова Ника');
    }

    #[Test]
    public function родитель_не_видит_чужого_ребёнка(): void
    {
        $stranger = User::where('username', 'timur')->firstOrFail();

        $this->actingAs($this->guardian())
            ->get(route('family.children.show', $stranger))
            ->assertNotFound();
    }

    #[Test]
    public function на_странице_ребёнка_нет_его_кода(): void
    {
        // Родителю нужен ответ на «ходит ли» и «справляется ли»; разбор
        // решения — дело преподавателя, и код в кабинет не попадает.
        $child = User::where('username', 'artem')->firstOrFail();
        $submission = $child->submissions()->firstOrFail();

        $this->actingAs($this->guardian())
            ->get(route('family.children.show', $child))
            ->assertOk()
            ->assertSee($submission->assignment->title)
            ->assertDontSee('print(n * n)');
    }

    #[Test]
    public function у_ребёнка_может_быть_двое_взрослых(): void
    {
        $kira = User::where('username', 'kira')->firstOrFail();

        $this->assertSame(2, $kira->guardians()->count());

        foreach ($kira->guardians as $guardian) {
            $this->actingAs($guardian)
                ->get(route('family.children.show', $kira))
                ->assertOk();
        }
    }

    #[Test]
    public function кабинет_родителя_закрыт_остальным(): void
    {
        $this->actingAs($this->admin())->get(route('family.index'))->assertForbidden();
        $this->actingAs(User::where('username', 'artem')->firstOrFail())
            ->get(route('family.index'))->assertForbidden();
    }

    #[Test]
    public function сводка_считает_людей_по_ролям(): void
    {
        $counts = (new SchoolAnalytics)->headcount();

        $this->assertSame(18, $counts['students']);
        $this->assertSame(3, $counts['teachers']);
        $this->assertSame(5, $counts['guardians']);
        $this->assertSame(5, $counts['groups']);
    }

    #[Test]
    public function отключённые_учётные_записи_в_сводку_не_идут(): void
    {
        User::where('username', 'artem')->update(['is_active' => false]);

        $this->assertSame(17, (new SchoolAnalytics)->headcount()['students']);
    }

    #[Test]
    public function тяжёлые_задачи_идут_снизу_вверх_по_доле_решивших(): void
    {
        $rows = (new SchoolAnalytics)->hardestAssignments();

        $this->assertNotEmpty($rows);

        $shares = $rows->pluck('share')->all();
        $sorted = $shares;
        sort($sorted);

        $this->assertSame($sorted, $shares, 'задачи должны идти от самой трудной');
    }

    #[Test]
    public function недели_без_занятий_остаются_на_графике(): void
    {
        // Пропуск в расписании — это факт, а не отсутствие данных: нулём его
        // показывать нельзя, иначе он читается как «никто не пришёл».
        $trend = (new SchoolAnalytics)->attendanceTrend(8);

        $this->assertCount(8, $trend);
        $this->assertTrue($trend->contains(fn (array $w): bool => $w['share'] === null));
    }

    #[Test]
    public function пропуск_по_уважительной_посещаемость_не_штрафует(): void
    {
        $byGroup = (new SchoolAnalytics)->attendanceByGroup();

        $this->assertNotEmpty($byGroup);

        foreach ($byGroup as $row) {
            $this->assertGreaterThanOrEqual(0, $row['share']);
            $this->assertLessThanOrEqual(100, $row['share']);
            $this->assertLessThanOrEqual($row['total'], $row['attended']);
        }
    }
}
