<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Group;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * API для мобильного приложения.
 *
 * Проверяется не только «отвечает ли», но и что наружу не уходит лишнего:
 * скрытые тест-кейсы и код ребёнка не должны утекать через API так же, как
 * они не утекают на сайте.
 */
class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    #[Test]
    public function вход_выдаёт_токен(): void
    {
        $response = $this->postJson(route('api.login'), [
            'username' => 'lebedeva',
            'password' => 'password',
            'device' => 'android',
        ])->assertOk();

        $this->assertNotEmpty($response->json('token'));
        $this->assertSame('teacher', $response->json('user.role'));
    }

    #[Test]
    public function неверный_пароль_токена_не_даёт(): void
    {
        $this->postJson(route('api.login'), [
            'username' => 'lebedeva',
            'password' => 'не тот',
            'device' => 'android',
        ])->assertStatus(422);
    }

    #[Test]
    public function отключённая_учётная_запись_не_пускается(): void
    {
        User::where('username', 'lebedeva')->update(['is_active' => false]);

        $this->postJson(route('api.login'), [
            'username' => 'lebedeva',
            'password' => 'password',
            'device' => 'android',
        ])->assertStatus(422);
    }

    #[Test]
    public function второй_вход_с_того_же_устройства_отзывает_прежний_токен(): void
    {
        // Иначе список токенов растёт при каждой переустановке приложения.
        $payload = ['username' => 'artem', 'password' => 'password', 'device' => 'android'];

        $this->postJson(route('api.login'), $payload)->assertOk();
        $this->postJson(route('api.login'), $payload)->assertOk();

        $this->assertSame(1, User::where('username', 'artem')->firstOrFail()
            ->tokens()->where('name', 'android')->count());
    }

    #[Test]
    public function без_токена_ничего_не_отдаётся(): void
    {
        $this->getJson(route('api.me'))->assertUnauthorized();
        $this->getJson(route('api.lessons.index'))->assertUnauthorized();
        $this->getJson(route('api.children.index'))->assertUnauthorized();
    }

    #[Test]
    public function роль_ограничивает_разделы(): void
    {
        Sanctum::actingAs(User::where('username', 'artem')->firstOrFail());

        $this->getJson(route('api.lessons.index'))->assertOk();
        $this->getJson(route('api.children.index'))->assertForbidden();
        $this->getJson(route('api.groups.index'))->assertForbidden();
    }

    #[Test]
    public function ученик_получает_свои_занятия(): void
    {
        Sanctum::actingAs(User::where('username', 'artem')->firstOrFail());

        $response = $this->getJson(route('api.lessons.index'))->assertOk();

        $titles = collect($response->json('data'))->pluck('title');

        $this->assertTrue($titles->contains('Ввод, вывод и числа'));
        // Занятие робототехники ученику Python не принадлежит.
        $this->assertFalse($titles->contains('Двоичный поиск'));
    }

    #[Test]
    public function скрытые_кейсы_через_api_не_утекают(): void
    {
        $assignment = Assignment::where('slug', 'square')->firstOrFail();
        $hidden = $assignment->tests()->where('is_hidden', true)->firstOrFail();

        Sanctum::actingAs(User::where('username', 'artem')->firstOrFail());

        $response = $this->getJson(route('api.assignments.show', $assignment))->assertOk();

        $this->assertSame(1, $response->json('assignment.hidden_tests'));
        $this->assertStringNotContainsString(
            (string) $hidden->stdin,
            json_encode($response->json('assignment.tests'), JSON_UNESCAPED_UNICODE),
        );
    }

    #[Test]
    public function сдача_уходит_в_очередь_и_отдаёт_идентификатор(): void
    {
        Http::fake(['wandbox.org/*' => Http::response(['status' => '0', 'program_output' => '4'])]);

        $assignment = Assignment::where('slug', 'square')->firstOrFail();
        $student = User::where('username', 'artem')->firstOrFail();
        Sanctum::actingAs($student);

        $response = $this->postJson(route('api.assignments.submit', $assignment), [
            'code' => 'print(1)',
        ])->assertStatus(202);

        $this->assertNotNull($response->json('data.id'));
        $this->assertTrue($response->json('data.pending'));
    }

    #[Test]
    public function чужую_работу_по_идентификатору_не_посмотреть(): void
    {
        $foreign = User::where('username', 'sofia')->firstOrFail()->submissions()->firstOrFail();

        Sanctum::actingAs(User::where('username', 'artem')->firstOrFail());

        $this->getJson(route('api.submissions.show', $foreign))->assertNotFound();
    }

    #[Test]
    public function родитель_видит_своих_и_не_видит_чужих(): void
    {
        Sanctum::actingAs(User::where('username', 'belova')->firstOrFail());

        $names = collect($this->getJson(route('api.children.index'))->assertOk()->json('data'))
            ->pluck('name');

        $this->assertTrue($names->contains('Белов Артём'));
        $this->assertFalse($names->contains('Зайцев Тимур'));

        $stranger = User::where('username', 'timur')->firstOrFail();
        $this->getJson(route('api.children.show', $stranger))->assertNotFound();
    }

    #[Test]
    public function код_ребёнка_через_api_родителю_не_отдаётся(): void
    {
        $child = User::where('username', 'artem')->firstOrFail();
        $submission = $child->submissions()->firstOrFail();

        Sanctum::actingAs(User::where('username', 'belova')->firstOrFail());

        $body = $this->getJson(route('api.children.show', $child))->assertOk()->content();

        $this->assertStringNotContainsString('print(n * n)', $body);
        $this->assertStringNotContainsString($submission->code, $body);
    }

    #[Test]
    public function преподаватель_отмечает_посещаемость(): void
    {
        $teacher = User::where('username', 'lebedeva')->firstOrFail();
        $group = Group::where('slug', 'python-1')->firstOrFail();
        $student = $group->students()->firstOrFail();

        Sanctum::actingAs($teacher);

        $this->postJson(route('api.journal.store'), [
            'group' => $group->slug,
            'date' => '2026-09-08',
            'marks' => [$student->id => 'late'],
        ])->assertOk()->assertJson(['saved' => 1]);

        $this->assertDatabaseHas('attendances', [
            'group_id' => $group->id,
            'user_id' => $student->id,
            'held_on' => '2026-09-08',
            'status' => 'late',
        ]);
    }

    #[Test]
    public function отметка_постороннему_не_ставится(): void
    {
        // Подменённое тело запроса не должно заводить в журнал того, кого
        // в группе нет.
        $teacher = User::where('username', 'lebedeva')->firstOrFail();
        $group = Group::where('slug', 'python-1')->firstOrFail();
        $stranger = User::where('username', 'ulyana')->firstOrFail();

        Sanctum::actingAs($teacher);

        $this->postJson(route('api.journal.store'), [
            'group' => $group->slug,
            'date' => '2026-09-08',
            'marks' => [$stranger->id => 'present'],
        ])->assertOk()->assertJson(['saved' => 0]);

        $this->assertDatabaseMissing('attendances', [
            'group_id' => $group->id,
            'user_id' => $stranger->id,
            'held_on' => '2026-09-08',
        ]);
    }

    #[Test]
    public function чужую_группу_в_журнале_не_открыть(): void
    {
        Sanctum::actingAs(User::where('username', 'lebedeva')->firstOrFail());

        $this->getJson(route('api.journal.show', ['group' => 'robots-1']))->assertNotFound();
    }

    #[Test]
    public function расписание_зависит_от_роли(): void
    {
        Sanctum::actingAs(User::where('username', 'artem')->firstOrFail());
        $student = collect($this->getJson(route('api.schedule'))->assertOk()->json('data'));

        Sanctum::actingAs(User::where('username', 'kovaleva')->firstOrFail());
        $teacher = collect($this->getJson(route('api.schedule'))->assertOk()->json('data'));

        $this->assertTrue($student->pluck('group')->contains('Питон-1'));
        $this->assertTrue($teacher->pluck('group')->contains('Роботы-1'));
        $this->assertFalse($teacher->pluck('group')->contains('Питон-1'));
    }

    #[Test]
    public function подписи_приходят_на_языке_из_заголовка(): void
    {
        // Приложение не держит своих словарей для статусов: сервер отдаёт их
        // уже переведёнными, и языки не разъезжаются между клиентом и сайтом.
        Sanctum::actingAs(User::where('username', 'lebedeva')->firstOrFail());

        $ru = $this->withHeader('Accept-Language', 'ru')
            ->getJson(route('api.journal.show', ['group' => 'python-1']))->json('statuses.0.label');

        $en = $this->withHeader('Accept-Language', 'en')
            ->getJson(route('api.journal.show', ['group' => 'python-1']))->json('statuses.0.label');

        $this->assertSame('На занятии', $ru);
        $this->assertSame('Present', $en);
    }

    #[Test]
    public function выход_отзывает_токен(): void
    {
        $token = $this->postJson(route('api.login'), [
            'username' => 'artem', 'password' => 'password', 'device' => 'android',
        ])->json('token');

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson(route('api.logout'))->assertOk();

        // Внутри теста приложение живёт между запросами, и страж помнит
        // разобранного пользователя. В бою каждый запрос — новый процесс,
        // поэтому здесь состояние сбрасывается вручную.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson(route('api.me'))->assertUnauthorized();
    }
}
