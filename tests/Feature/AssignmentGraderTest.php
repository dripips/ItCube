<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentTest;
use App\Models\User;
use App\Services\AssignmentGrader;
use App\Services\CodeRunner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssignmentGraderTest extends TestCase
{
    use RefreshDatabase;

    private function grader(): AssignmentGrader
    {
        return new AssignmentGrader(new CodeRunner);
    }

    private function student(): User
    {
        return User::factory()->create(['role' => Role::Student]);
    }

    /**
     * Площадка отвечает по очереди тем, что перечислено.
     *
     * @param  list<string>  $outputs
     */
    private function fakeOutputs(array $outputs): void
    {
        $queue = array_map(fn (string $out) => Http::response([
            'status' => '0',
            'program_output' => $out,
            'program_error' => '',
            'compiler_error' => '',
        ]), $outputs);

        Http::fake(['wandbox.org/*' => Http::sequence($queue)]);
    }

    private function assignmentWithTests(array $cases): Assignment
    {
        $assignment = Assignment::factory()->create(['language' => 'python']);

        foreach ($cases as $position => $case) {
            AssignmentTest::factory()->create([
                'assignment_id' => $assignment->id,
                'stdin' => $case['stdin'] ?? '',
                'expected_output' => $case['expected'],
                'is_hidden' => $case['hidden'] ?? false,
                'points' => $case['points'] ?? 1,
                'position' => $position,
            ]);
        }

        return $assignment->fresh();
    }

    #[Test]
    public function все_кейсы_сошлись_работа_зачтена(): void
    {
        $assignment = $this->assignmentWithTests([
            ['stdin' => '2', 'expected' => '4', 'points' => 2],
            ['stdin' => '3', 'expected' => '9', 'points' => 3],
        ]);
        $this->fakeOutputs(['4', '9']);

        $submission = $this->grader()->grade($assignment, $this->student(), 'x = int(input()); print(x*x)');

        $this->assertSame(SubmissionStatus::Passed, $submission->status);
        $this->assertSame(2, $submission->passed_count);
        $this->assertSame(2, $submission->total_count);
        $this->assertSame(5, $submission->score);
    }

    #[Test]
    public function один_кейс_не_сошёлся_баллы_частичные(): void
    {
        $assignment = $this->assignmentWithTests([
            ['stdin' => '2', 'expected' => '4', 'points' => 2],
            ['stdin' => '3', 'expected' => '9', 'points' => 3],
        ]);
        $this->fakeOutputs(['4', '6']);

        $submission = $this->grader()->grade($assignment, $this->student(), 'какой-то код');

        $this->assertSame(SubmissionStatus::Failed, $submission->status);
        $this->assertSame(1, $submission->passed_count);
        $this->assertSame(2, $submission->score);
    }

    #[Test]
    public function провал_первого_кейса_не_останавливает_проверку(): void
    {
        $assignment = $this->assignmentWithTests([
            ['expected' => 'а'],
            ['expected' => 'б'],
            ['expected' => 'в'],
        ]);
        $this->fakeOutputs(['мимо', 'б', 'в']);

        $submission = $this->grader()->grade($assignment, $this->student(), 'код');

        $this->assertCount(3, $submission->test_results);
        $this->assertSame(2, $submission->passed_count);
    }

    #[Test]
    public function скрытый_кейс_не_раскрывает_ни_входа_ни_ответа(): void
    {
        $assignment = $this->assignmentWithTests([
            ['stdin' => 'видно', 'expected' => 'ок'],
            ['stdin' => 'секрет', 'expected' => 'тайна', 'hidden' => true],
        ]);
        $this->fakeOutputs(['ок', 'мимо']);

        $submission = $this->grader()->grade($assignment, $this->student(), 'код');

        [$open, $hidden] = $submission->test_results;

        $this->assertSame('видно', $open['stdin']);
        $this->assertArrayNotHasKey('stdin', $hidden);
        $this->assertArrayNotHasKey('expected', $hidden);
        $this->assertArrayNotHasKey('actual', $hidden);
        $this->assertFalse($hidden['passed']);
        $this->assertTrue($hidden['hidden']);

        // И в целом нигде: ни в карточке, ни в сводном выводе.
        $this->assertStringNotContainsString('секрет', json_encode($submission->test_results, JSON_UNESCAPED_UNICODE));
        $this->assertStringNotContainsString('тайна', (string) $submission->output);
    }

    #[Test]
    public function хвостовой_перевод_строки_не_считается_ошибкой(): void
    {
        $assignment = $this->assignmentWithTests([['expected' => "42\n"]]);
        $this->fakeOutputs(['42   ']);

        $submission = $this->grader()->grade($assignment, $this->student(), 'print(42)');

        $this->assertSame(SubmissionStatus::Passed, $submission->status);
    }

    #[Test]
    public function регистр_сверяется_точно(): void
    {
        $assignment = $this->assignmentWithTests([['expected' => 'Привет']]);
        $this->fakeOutputs(['привет']);

        $submission = $this->grader()->grade($assignment, $this->student(), 'код');

        $this->assertSame(SubmissionStatus::Failed, $submission->status);
    }

    #[Test]
    public function отклонённый_код_не_уходит_на_площадку_ни_разу(): void
    {
        $assignment = $this->assignmentWithTests([
            ['expected' => 'а'],
            ['expected' => 'б'],
        ]);
        Http::fake();

        $submission = $this->grader()->grade($assignment, $this->student(), "import subprocess\n");

        $this->assertSame(SubmissionStatus::Blocked, $submission->status);
        $this->assertSame(0, $submission->score);
        $this->assertSame(0, $submission->passed_count);
        Http::assertNothingSent();
    }

    #[Test]
    public function код_который_не_собрался_помечен_ошибкой_а_не_провалом(): void
    {
        $assignment = $this->assignmentWithTests([['expected' => 'а'], ['expected' => 'б']]);
        Http::fake(['wandbox.org/*' => Http::response([
            'status' => '1',
            'program_output' => '',
            'program_error' => '',
            'compiler_error' => 'SyntaxError',
        ])]);

        $submission = $this->grader()->grade($assignment, $this->student(), 'def(');

        $this->assertSame(SubmissionStatus::Error, $submission->status);
        $this->assertStringContainsString('SyntaxError', (string) $submission->output);
    }

    #[Test]
    public function номер_попытки_растёт(): void
    {
        $assignment = $this->assignmentWithTests([['expected' => 'ок']]);
        $student = $this->student();

        // Одна последовательность на обе попытки: Http::fake() прежние заглушки
        // не заменяет, а добавляется к ним, и вторая настройка не помогла бы.
        $this->fakeOutputs(['мимо', 'ок']);

        $first = $this->grader()->grade($assignment, $student, 'раз');
        $second = $this->grader()->grade($assignment, $student, 'два');

        $this->assertSame(1, $first->attempt_number);
        $this->assertSame(2, $second->attempt_number);
        $this->assertSame(SubmissionStatus::Passed, $second->status);
    }

    #[Test]
    public function лучшая_попытка_выбирается_по_баллам(): void
    {
        $assignment = $this->assignmentWithTests([
            ['expected' => 'а', 'points' => 1],
            ['expected' => 'б', 'points' => 1],
        ]);
        $student = $this->student();

        $this->fakeOutputs(['а', 'мимо', 'а', 'б', 'мимо', 'мимо']);

        $this->grader()->grade($assignment, $student, 'раз');
        $best = $this->grader()->grade($assignment, $student, 'два');
        $this->grader()->grade($assignment, $student, 'три');

        $this->assertTrue($assignment->bestSubmissionFor($student)->is($best));
    }

    #[Test]
    public function максимум_баллов_считает_и_скрытые_кейсы(): void
    {
        $assignment = $this->assignmentWithTests([
            ['expected' => 'а', 'points' => 2],
            ['expected' => 'б', 'points' => 3, 'hidden' => true],
        ]);

        $this->assertSame(5, $assignment->maxScore());
    }
}
