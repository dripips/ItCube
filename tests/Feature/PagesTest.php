<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Direction;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Quiz;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Каждая страница должна открываться на настоящих данных.
 *
 * Тест намеренно идёт по демонстрационному наполнению, а не по фабрикам:
 * половина ошибок в шаблонах вылезает именно на связях, которых у одиночной
 * фабрики нет.
 */
class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    private function student(): User
    {
        return Group::where('slug', 'python-1')->firstOrFail()->students()->firstOrFail();
    }

    private function teacher(): User
    {
        return User::where('username', 'lebedeva')->firstOrFail();
    }

    #[Test]
    public function публичные_страницы_открываются_гостю(): void
    {
        $direction = Direction::where('slug', 'python')->firstOrFail();
        $post = Post::firstOrFail();

        $this->get(route('home'))->assertOk()->assertSee('ItCube');
        $this->get(route('directions.index'))->assertOk();
        $this->get(route('directions.show', $direction))->assertOk()->assertSee($direction->name);
        $this->get(route('posts.index'))->assertOk();
        $this->get(route('posts.show', $post))->assertOk()->assertSee($post->title);
        $this->get(route('login'))->assertOk();
    }

    #[Test]
    public function кабинет_ученика_гостю_закрыт(): void
    {
        $this->get(route('learn.index'))->assertRedirect(route('login'));
        $this->get(route('teach.groups.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function страницы_ученика_открываются(): void
    {
        $student = $this->student();
        $lesson = Lesson::where('slug', 'input-output')->firstOrFail();
        $assignment = Assignment::where('slug', 'square')->firstOrFail();
        $quiz = Quiz::firstOrFail();
        $assessment = Assessment::firstOrFail();

        $this->actingAs($student)->get(route('learn.index'))->assertOk();
        $this->actingAs($student)->get(route('learn.schedule'))->assertOk();
        $this->actingAs($student)->get(route('learn.lessons.show', $lesson))->assertOk()->assertSee($lesson->title);
        $this->actingAs($student)->get(route('learn.assignments.show', $assignment))->assertOk()->assertSee($assignment->title);
        $this->actingAs($student)->get(route('learn.quizzes.show', $quiz))->assertOk();
        $this->actingAs($student)->get(route('learn.assessments'))->assertOk();
        $this->actingAs($student)->get(route('learn.assessments.show', $assessment))->assertOk();
    }

    #[Test]
    public function страницы_преподавателя_открываются(): void
    {
        $teacher = $this->teacher();
        $group = Group::where('slug', 'python-1')->firstOrFail();
        $assessment = Assessment::firstOrFail();

        $this->actingAs($teacher)->get(route('teach.groups.index'))->assertOk();
        $this->actingAs($teacher)->get(route('teach.groups.show', $group))->assertOk()->assertSee($group->name);
        $this->actingAs($teacher)->get(route('teach.journal.index'))->assertOk();
        $this->actingAs($teacher)->get(route('teach.assessments.index'))->assertOk();
        $this->actingAs($teacher)->get(route('teach.assessments.show', $assessment))->assertOk();
    }

    #[Test]
    public function ученику_в_кабинет_преподавателя_нельзя(): void
    {
        $this->actingAs($this->student())->get(route('teach.groups.index'))->assertForbidden();
    }

    #[Test]
    public function преподавателю_в_кабинет_ученика_нельзя(): void
    {
        $this->actingAs($this->teacher())->get(route('learn.index'))->assertForbidden();
    }

    #[Test]
    public function чужое_занятие_ученику_не_видно(): void
    {
        // Ученик из группы Python не должен открыть занятие робототехники.
        $student = $this->student();
        $foreign = Lesson::where('slug', 'binary-search')->firstOrFail();

        $this->actingAs($student)->get(route('learn.lessons.show', $foreign))->assertNotFound();
    }

    #[Test]
    public function чужую_группу_преподаватель_не_открывает(): void
    {
        $foreign = Group::where('slug', 'robots-1')->firstOrFail();

        $this->actingAs($this->teacher())->get(route('teach.groups.show', $foreign))->assertNotFound();
    }

    #[Test]
    public function вход_по_логину_работает(): void
    {
        $this->post(route('login'), ['username' => 'lebedeva', 'password' => 'password'])
            ->assertRedirect(route('teach.groups.index'));

        $this->assertAuthenticated();
    }

    #[Test]
    public function отключённая_учётная_запись_не_пускается(): void
    {
        $this->teacher()->update(['is_active' => false]);

        $this->post(route('login'), ['username' => 'lebedeva', 'password' => 'password'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    #[Test]
    public function язык_переключается_и_запоминается_в_профиле(): void
    {
        $student = $this->student();

        $this->actingAs($student)->post(route('locale'), ['locale' => 'en'])->assertRedirect();

        $this->assertSame('en', $student->fresh()->locale);
    }

    #[Test]
    public function выдуманный_язык_отклоняется(): void
    {
        $this->post(route('locale'), ['locale' => 'эльфийский'])->assertSessionHasErrors('locale');
    }
}
