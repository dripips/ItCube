<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Enums\Role;
use App\Enums\SubmissionStatus;
use App\Models\AnswerOption;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\Assignment;
use App\Models\AssignmentTest;
use App\Models\Attendance;
use App\Models\Direction;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Наполнение для демонстрации: учебный центр на четыре направления.
 *
 * Почты нарочно на example.com — рабочие адреса в демонстрационные данные
 * попадать не должны ни при каких обстоятельствах.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->person('admin', 'Вадим', 'Бобков', Role::Admin);

        $teachers = [
            'python' => $this->person('lebedeva', 'Анна', 'Лебедева', Role::Teacher, 'Ведёт Python и алгоритмы. Готовила команды к региональному этапу олимпиады.'),
            'web' => $this->person('sorokin', 'Игорь', 'Сорокин', Role::Teacher, 'Веб-разработка и всё, что связано с браузером.'),
            'robots' => $this->person('kovaleva', 'Мария', 'Ковалёва', Role::Teacher, 'Робототехника, микроконтроллеры, соревнования.'),
        ];

        $students = $this->students();

        $directions = $this->directions($teachers);
        $groups = $this->groups($directions, $teachers, $students);

        $this->pythonCourse($directions['python'], $teachers['python'], $groups['python-1'], $students);
        $this->webCourse($directions['web'], $teachers['web']);
        $this->algoCourse($directions['algo'], $teachers['python']);

        $this->attendance($groups);
        $this->guardians($students);
        $this->news($admin, $teachers['python']);
    }

    private function person(string $username, string $first, string $last, Role $role, ?string $bio = null): User
    {
        return User::updateOrCreate(
            ['username' => $username],
            [
                'name' => "$first $last",
                'first_name' => $first,
                'last_name' => $last,
                'role' => $role,
                'bio' => $bio,
                'email' => "$username@example.com",
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );
    }

    /** @return array<int, User> */
    private function students(): array
    {
        $names = [
            ['artem', 'Артём', 'Белов'], ['sofia', 'София', 'Волкова'], ['maks', 'Максим', 'Гусев'],
            ['kira', 'Кира', 'Данилова'], ['lev', 'Лев', 'Ершов'], ['nika', 'Ника', 'Жукова'],
            ['timur', 'Тимур', 'Зайцев'], ['alisa', 'Алиса', 'Иванова'], ['egor', 'Егор', 'Козлов'],
            ['vera', 'Вера', 'Лапина'], ['savva', 'Савва', 'Мельник'], ['dasha', 'Дарья', 'Носова'],
            ['ilya', 'Илья', 'Орлов'], ['polina', 'Полина', 'Панова'], ['roman', 'Роман', 'Рыбаков'],
            ['mila', 'Мила', 'Соколова'], ['fedor', 'Фёдор', 'Тихонов'], ['ulyana', 'Ульяна', 'Фомина'],
        ];

        return array_map(fn (array $n): User => $this->person($n[0], $n[1], $n[2], Role::Student), $names);
    }

    /** @return array<string, Direction> */
    private function directions(array $teachers): array
    {
        $rows = [
            ['python', 'Программирование на Python', '11–15', 'С нуля до собственной программы: переменные, ветвления, циклы, списки и функции. Каждая тема заканчивается задачей, которая проверяется автоматически.', $teachers['python'], 1],
            ['web', 'Веб-разработка', '13–17', 'HTML, CSS и JavaScript. К концу курса ученик делает страницу, которая работает в браузере и не разваливается на телефоне.', $teachers['web'], 2],
            ['algo', 'Алгоритмы и олимпиады', '14–18', 'Разбор задач регионального уровня: перебор, сортировки, динамическое программирование. Языки на выбор — Python или C++.', $teachers['python'], 3],
            ['robots', 'Робототехника', '10–14', 'Сборка и программирование роботов: датчики, моторы, простая автоматика.', $teachers['robots'], 4],
        ];

        $result = [];

        foreach ($rows as [$slug, $name, $age, $description, $teacher, $position]) {
            $result[$slug] = Direction::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'age_range' => $age,
                    'description' => $description,
                    'teacher_id' => $teacher->id,
                    'position' => $position,
                    'published' => true,
                ],
            );
        }

        return $result;
    }

    /** @return array<string, Group> */
    private function groups(array $directions, array $teachers, array $students): array
    {
        $rows = [
            ['python-1', 'Питон-1', $directions['python'], $teachers['python'], [[2, '16:00', '17:30'], [5, '16:00', '17:30']], 0, 8],
            ['python-2', 'Питон-2', $directions['python'], $teachers['python'], [[3, '18:00', '19:30']], 8, 13],
            ['web-1', 'Веб-1', $directions['web'], $teachers['web'], [[1, '17:00', '18:30'], [4, '17:00', '18:30']], 4, 12],
            ['algo-1', 'Алгоритмы', $directions['algo'], $teachers['python'], [[6, '11:00', '13:00']], 10, 18],
            ['robots-1', 'Роботы-1', $directions['robots'], $teachers['robots'], [[3, '15:00', '16:30']], 13, 18],
        ];

        $result = [];

        foreach ($rows as [$slug, $name, $direction, $teacher, $slots, $from, $to]) {
            $group = Group::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'direction_id' => $direction->id,
                    'teacher_id' => $teacher->id,
                    'starts_on' => Carbon::create(2026, 9, 1),
                    'is_archived' => false,
                ],
            );

            $group->schedules()->delete();

            foreach ($slots as [$day, $start, $end]) {
                Schedule::create([
                    'group_id' => $group->id,
                    'day_of_week' => $day,
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'room' => 'каб. '.(200 + $day),
                ]);
            }

            $group->students()->syncWithoutDetaching(
                collect(array_slice($students, $from, $to - $from))
                    ->mapWithKeys(fn (User $s): array => [$s->id => ['joined_on' => '2026-09-01']])
                    ->all()
            );

            $result[$slug] = $group;
        }

        return $result;
    }

    private function pythonCourse(Direction $direction, User $teacher, Group $group, array $students): void
    {
        $basics = $this->subject($direction, 'basics', 'Основы языка', 1);
        $data = $this->subject($direction, 'data', 'Списки и словари', 2);

        $lesson = $this->lesson($basics, $teacher, 'input-output', 'Ввод, вывод и числа', 1,
            'Первая программа: прочитать число, что-то с ним сделать, напечатать ответ.',
            <<<'TEXT'
            Любая программа на олимпиаде и в этом курсе устроена одинаково: прочитать данные, посчитать, напечатать ответ.

            Читаем строку и превращаем её в число:

            n = int(input())

            input() всегда возвращает строку — даже когда там написано 42. Поэтому int() обязателен, иначе 42 + 1 превратится в ошибку, а не в 43.

            Печатаем:

            print(n + 1)

            Обратите внимание: print сам добавляет перевод строки в конце. Отдельно его писать не нужно, а лишний перевод строки проверку не сломает — хвостовые пробелы и переводы при сверке отбрасываются.
            TEXT);

        $square = $this->assignment($lesson, 'square', 'Квадрат числа', 'python', Difficulty::Easy,
            "Прочитайте целое число и напечатайте его квадрат.\n\nПодсказка: возведение в степень в Python — это **, но никто не запрещает написать n * n.",
            "n = int(input())\n# ваш код\n",
            [['2', '4', false], ['3', '9', false], ['0', '0', false], ['-5', '25', false], ['1000', '1000000', true]]);

        $even = $this->assignment($lesson, 'even-odd', 'Чётное или нечётное', 'python', Difficulty::Easy,
            "Прочитайте целое число. Напечатайте «чётное», если оно делится на два без остатка, и «нечётное» в противном случае.\n\nРегистр важен: проверка сверяет ответ точно.",
            "n = int(input())\n",
            [['4', 'чётное', false], ['7', 'нечётное', false], ['0', 'чётное', false], ['-3', 'нечётное', true]]);

        $second = $this->lesson($data, $teacher, 'lists', 'Списки: сумма и максимум', 2,
            'Как обойти список и не написать при этом цикл на десять строк.',
            <<<'TEXT'
            Список — это несколько значений под одним именем.

            numbers = [4, 8, 15, 16, 23, 42]

            Сумму и максимум считать вручную не нужно, для этого есть sum() и max(). Ручной цикл понадобится, когда условие сложнее: например, сумма только чётных.

            Чтобы прочитать список из одной строки:

            numbers = [int(x) for x in input().split()]

            split() режет строку по пробелам, а int() применяется к каждому куску.
            TEXT);

        $this->assignment($second, 'sum-even', 'Сумма чётных', 'python', Difficulty::Medium,
            "В первой строке дано число n — сколько будет чисел. Во второй строке через пробел даны сами числа.\n\nНапечатайте сумму тех из них, которые делятся на два без остатка. Если таких нет, напечатайте 0.",
            "n = int(input())\nnumbers = [int(x) for x in input().split()]\n",
            [["4\n1 2 3 4\n", '6', false], ["3\n1 3 5\n", '0', false], ["5\n2 4 6 8 10\n", '30', false], ["1\n-4\n", '-4', true]]);

        $quiz = Quiz::updateOrCreate(
            ['lesson_id' => $lesson->id, 'title' => 'Проверка: ввод и типы'],
            [
                'author_id' => $teacher->id,
                'description' => 'Пять вопросов на то, что легко перепутать в первый месяц.',
                'time_limit_minutes' => 10,
                'attempts_allowed' => 2,
                'published' => true,
            ],
        );

        $this->questions($quiz, [
            ['Что возвращает input() без всякой обработки?', QuestionType::Single,
                [['Строку', true], ['Целое число', false], ['Дробное число', false], ['Список', false]],
                'input() всегда возвращает строку. Даже когда пользователь ввёл 42.'],
            ['Какие выражения дадут 8 при n = 3?', QuestionType::Multiple,
                [['2 ** 3', true], ['n + 5', true], ['n * 3', false], ['n ** 2', false]],
                null],
            ['Что напечатает print(int("07") + 1)?', QuestionType::Text,
                [['8', true]],
                'Ведущий ноль в строке на значение не влияет: int("07") — это 8 после прибавления единицы.'],
            ['Чем отличается print(a, b) от print(a + b), если a и b — числа?', QuestionType::Single,
                [['Первое напечатает два числа через пробел, второе — их сумму', true],
                    ['Ничем, это одно и то же', false],
                    ['Первое вызовет ошибку', false],
                    ['Второе напечатает числа через запятую', false]],
                null],
            ['Что делает split() у строки?', QuestionType::Single,
                [['Режет строку на части по пробелам', true],
                    ['Убирает пробелы по краям', false],
                    ['Превращает строку в число', false],
                    ['Соединяет список в строку', false]],
                'Для обрезки краёв есть strip(), для склейки — join().'],
        ]);

        $assessment = Assessment::updateOrCreate(
            ['group_id' => $group->id, 'title' => 'Контрольная за первый модуль'],
            [
                'author_id' => $teacher->id,
                'description' => 'Две задачи и тест. На работу сорок пять минут.',
                'opens_at' => now()->subHours(2),
                'closes_at' => now()->addDays(2),
                'duration_minutes' => 45,
                'published' => true,
            ],
        );

        $assessment->items()->delete();

        foreach ([[$square, 4], [$even, 3], [$quiz, 5]] as $position => [$work, $points]) {
            AssessmentItem::create([
                'assessment_id' => $assessment->id,
                'itemable_type' => $work::class,
                'itemable_id' => $work->id,
                'points' => $points,
                'position' => $position,
            ]);
        }

        $this->submissions($square, $even, $group);
        $this->quizAttempts($quiz, $group);
    }

    private function webCourse(Direction $direction, User $teacher): void
    {
        $subject = $this->subject($direction, 'markup', 'Разметка и стили', 1);

        $lesson = $this->lesson($subject, $teacher, 'first-page', 'Первая страница', 1,
            'Из чего состоит страница и почему теги закрывают.',
            "Страница — это дерево. У дерева есть корень (html), голова (head) и тело (body).\n\nВ голову идёт то, чего не видно: кодировка, заголовок вкладки, подключение стилей. В тело — то, что человек читает.\n\nЗакрывающий тег нужен не всем элементам, но привычка закрывать всё спасает от часа поиска съехавшей вёрстки.");

        $this->assignment($lesson, 'greeting', 'Приветствие', 'javascript', Difficulty::Easy,
            "Прочитайте имя из ввода и напечатайте «Привет, имя!».\n\nВ JavaScript ввод читается так:\n\nconst name = require('fs').readFileSync(0, 'utf8').trim();",
            "const name = require('fs').readFileSync(0, 'utf8').trim();\n",
            [["Аня\n", 'Привет, Аня!', false], ["Игорь\n", 'Привет, Игорь!', false], ["Лев\n", 'Привет, Лев!', true]]);
    }

    private function algoCourse(Direction $direction, User $teacher): void
    {
        $subject = $this->subject($direction, 'search', 'Перебор и поиск', 1);

        $lesson = $this->lesson($subject, $teacher, 'binary-search', 'Двоичный поиск', 1,
            'Как найти число в миллионе значений за двадцать шагов.',
            "Перебор по очереди — это до миллиона проверок. Двоичный поиск делит отрезок пополам, и на миллионе ему нужно не больше двадцати шагов.\n\nУсловие ровно одно: массив отсортирован. Без сортировки делить пополам бессмысленно — неизвестно, в какой половине искать.\n\nСамая частая ошибка — граница цикла. Пока left <= right, а не left < right: иначе последний оставшийся элемент не проверяется.");

        $this->assignment($lesson, 'binary-search', 'Найти позицию', 'cpp', Difficulty::Hard,
            "В первой строке n — размер массива. Во второй n отсортированных по возрастанию чисел. В третьей — искомое число x.\n\nНапечатайте позицию x (нумерация с единицы) или -1, если такого числа нет.\n\nМассив может быть большим: перебор по очереди не пройдёт по времени.",
            "#include <iostream>\n#include <vector>\n\nint main() {\n    int n;\n    std::cin >> n;\n    std::vector<int> a(n);\n    for (int i = 0; i < n; ++i) std::cin >> a[i];\n    int x;\n    std::cin >> x;\n    // ваш код\n    return 0;\n}\n",
            [["5\n1 3 5 7 9\n5\n", '3', false], ["5\n1 3 5 7 9\n4\n", '-1', false], ["1\n42\n42\n", '1', false], ["6\n2 4 6 8 10 12\n12\n", '6', true]]);
    }

    private function subject(Direction $direction, string $slug, string $name, int $position): Subject
    {
        return Subject::updateOrCreate(
            ['direction_id' => $direction->id, 'slug' => $slug],
            ['name' => $name, 'position' => $position],
        );
    }

    private function lesson(Subject $subject, User $teacher, string $slug, string $title, int $position, string $summary, string $content): Lesson
    {
        return Lesson::updateOrCreate(
            ['subject_id' => $subject->id, 'slug' => $slug],
            [
                'teacher_id' => $teacher->id,
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'code_language' => 'python',
                'position' => $position,
                'published_at' => now()->subWeek(),
            ],
        );
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: bool}>  $cases
     */
    private function assignment(Lesson $lesson, string $slug, string $title, string $language, Difficulty $difficulty, string $instructions, string $starter, array $cases): Assignment
    {
        $assignment = Assignment::updateOrCreate(
            ['lesson_id' => $lesson->id, 'slug' => $slug],
            [
                'title' => $title,
                'language' => $language,
                'difficulty' => $difficulty,
                'instructions' => $instructions,
                'starter_code' => $starter,
                'published' => true,
            ],
        );

        $assignment->tests()->delete();

        foreach ($cases as $position => [$stdin, $expected, $hidden]) {
            AssignmentTest::create([
                'assignment_id' => $assignment->id,
                'stdin' => $stdin,
                'expected_output' => $expected,
                'is_hidden' => $hidden,
                'points' => $hidden ? 2 : 1,
                'position' => $position,
            ]);
        }

        return $assignment->fresh();
    }

    /**
     * @param  array<int, array{0: string, 1: QuestionType, 2: array<int, array{0: string, 1: bool}>, 3: ?string}>  $rows
     */
    private function questions(Quiz $quiz, array $rows): void
    {
        $quiz->questions()->delete();

        foreach ($rows as $position => [$text, $type, $options, $explanation]) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'type' => $type,
                'text' => $text,
                'explanation' => $explanation,
                'points' => $type === QuestionType::Multiple ? 2 : 1,
                'position' => $position,
            ]);

            foreach ($options as $index => [$optionText, $correct]) {
                AnswerOption::create([
                    'question_id' => $question->id,
                    'text' => $optionText,
                    'is_correct' => $correct,
                    'position' => $index,
                ]);
            }
        }
    }

    /**
     * Сданные работы для ведомости.
     *
     * Кейсы здесь не запускаются: обращаться к внешней площадке при наполнении
     * базы значило бы ждать минуты и зависеть от чужой доступности.
     */
    private function submissions(Assignment $square, Assignment $even, Group $group): void
    {
        $students = $group->students()->orderBy('id')->get();

        foreach ($students as $index => $student) {
            $this->fakeSubmission($square, $student, match ($index % 4) {
                0 => 1.0,
                1 => 1.0,
                2 => 0.6,
                default => 0.0,
            }, 12 - $index % 7);

            if ($index % 3 !== 2) {
                $this->fakeSubmission($even, $student, $index % 2 === 0 ? 1.0 : 0.75, 5 - $index % 5);
            }
        }
    }

    private function fakeSubmission(Assignment $assignment, User $student, float $share, int $daysAgo = 0): void
    {
        $tests = $assignment->tests()->get();
        $passCount = (int) round($tests->count() * $share);

        $rows = [];
        $score = 0;
        $passed = 0;

        foreach ($tests as $index => $test) {
            $ok = $index < $passCount;
            $passed += $ok ? 1 : 0;
            $score += $ok ? $test->points : 0;

            $row = [
                'id' => $test->id,
                'name' => 'Тест '.($index + 1),
                'hidden' => (bool) $test->is_hidden,
                'passed' => $ok,
                'points' => $ok ? $test->points : 0,
                'max_points' => $test->points,
            ];

            if (! $test->is_hidden) {
                $row += [
                    'stdin' => $test->stdin,
                    'expected' => $test->expected_output,
                    'actual' => $ok ? $test->expected_output : '0',
                    'runtime_ms' => random_int(1800, 4200),
                ];
            }

            $rows[] = $row;
        }

        Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => $student->id, 'attempt_number' => 1],
            [
                'code' => $assignment->starter_code."print(n * n)\n",
                'status' => $passed === $tests->count() ? SubmissionStatus::Passed : SubmissionStatus::Failed,
                'output' => $passed === $tests->count() ? 'Все тесты пройдены' : '0',
                'test_results' => $rows,
                'passed_count' => $passed,
                'total_count' => $tests->count(),
                'score' => $score,
                'runtime_ms' => random_int(8000, 16000),
                // Даты разнесены по дням: иначе график сдач за две недели
                // превращается в одну свечу и ничего не показывает.
                'created_at' => now()->subDays($daysAgo)->setTime(random_int(15, 20), random_int(0, 59)),
                'updated_at' => now()->subDays($daysAgo),
            ],
        );
    }

    /**
     * Пройденные тесты для ведомости.
     *
     * Ответы записываются настоящие: ведомость считает баллы по ним, а не по
     * заранее проставленной сумме, и подделка суммы скрыла бы ошибку в расчёте.
     */
    private function quizAttempts(Quiz $quiz, Group $group): void
    {
        $questions = $quiz->questions()->with('options')->get();
        $maxScore = (int) $questions->sum('points');

        foreach ($group->students()->orderBy('id')->get() as $index => $student) {
            if ($index % 5 === 3) {
                continue;
            }

            $attempt = $quiz->attempts()->updateOrCreate(
                ['user_id' => $student->id],
                ['started_at' => now()->subDays(2), 'submitted_at' => now()->subDays(2)->addMinutes(7), 'max_score' => $maxScore],
            );

            $score = 0;

            foreach ($questions as $position => $question) {
                // Кто-то ошибается на втором вопросе, кто-то на четвёртом:
                // одинаковые работы у всей группы выглядели бы подделкой.
                $correct = ($position + $index) % 4 !== 0;
                $ids = $correct
                    ? $question->correctOptionIds()
                    : $question->options->where('is_correct', false)->take(1)->pluck('id')->all();

                $score += $correct ? $question->points : 0;

                $attempt->answers()->updateOrCreate(
                    ['question_id' => $question->id],
                    [
                        'chosen_option_ids' => $question->type === QuestionType::Text ? null : $ids,
                        'text_answer' => $question->type === QuestionType::Text ? ($correct ? '8' : '7') : null,
                        'is_correct' => $correct,
                        'points_awarded' => $correct ? $question->points : 0,
                    ],
                );
            }

            $attempt->update(['score' => $score]);
        }
    }

    private function attendance(array $groups): void
    {
        foreach ($groups as $group) {
            $days = $group->schedules->pluck('day_of_week');

            if ($days->isEmpty()) {
                continue;
            }

            foreach ($group->students as $student) {
                for ($weeksAgo = 4; $weeksAgo >= 0; $weeksAgo--) {
                    foreach ($days as $day) {
                        $date = Carbon::today()->subWeeks($weeksAgo)->startOfWeek()->addDays($day - 1);

                        if ($date->isFuture()) {
                            continue;
                        }

                        Attendance::updateOrCreate(
                            ['group_id' => $group->id, 'user_id' => $student->id, 'held_on' => $date->toDateString()],
                            ['status' => $this->mark($student->id + $weeksAgo + $day)],
                        );
                    }
                }
            }
        }
    }

    /** Раскладка отметок без случайности: одна и та же база при каждом прогоне. */
    private function mark(int $seed): AttendanceStatus
    {
        return match ($seed % 9) {
            0 => AttendanceStatus::Absent,
            3 => AttendanceStatus::Late,
            6 => AttendanceStatus::Excused,
            default => AttendanceStatus::Present,
        };
    }

    /**
     * Взрослые, закреплённые за учениками.
     *
     * У одного двое детей, у другого ребёнок один, а у третьего ребёнка двое
     * взрослых: связь многие-ко-многим заведена именно ради этих случаев, и
     * в демонстрационных данных они должны встречаться, иначе её никто не
     * проверит.
     *
     * @param  array<int, User>  $students
     */
    private function guardians(array $students): void
    {
        $rows = [
            ['belova', 'Ирина', 'Белова', 'мать', [0, 5]],
            ['volkov', 'Сергей', 'Волков', 'отец', [1]],
            ['guseva', 'Наталья', 'Гусева', 'мать', [2]],
            ['danilov', 'Пётр', 'Данилов', 'отец', [3]],
            ['danilova', 'Ольга', 'Данилова', 'мать', [3]],
        ];

        foreach ($rows as [$username, $first, $last, $relation, $indexes]) {
            $guardian = $this->person($username, $first, $last, Role::Guardian);

            $guardian->children()->syncWithoutDetaching(
                collect($indexes)
                    ->mapWithKeys(fn (int $i): array => [$students[$i]->id => ['relation' => $relation]])
                    ->all()
            );
        }
    }

    private function news(User $admin, User $teacher): void
    {
        $rows = [
            ['otkrytyy-urok', 'Открытый урок по Python 20 сентября',
                'Приходите посмотреть, как проходит занятие: разберём задачу и запустим её на проверке.',
                "Занятие открытое: можно прийти с родителями и просто посидеть в классе.\n\nРазберём задачу про сумму чётных чисел, напишем решение целиком и отправим на проверку — увидите, как выглядит разбор по наборам данных, включая скрытые.\n\nЗаписываться не нужно, но напишите преподавателю, если придёте большой компанией: посадочных мест двенадцать.", 3],
            ['olimpiada-rezultaty', 'Четверо наших вышли в региональный этап',
                'Поздравляем: из шести участников школьного этапа четверо прошли дальше.',
                "Из шести участников школьного этапа дальше прошли четверо.\n\nРазбор задач, на которых потеряли баллы, будет в субботу на «Алгоритмах». Приносите свои решения — смотреть чужие полезно, но своё разбирать полезнее.", 10],
            ['novyy-kabinet', 'Новый кабинет для робототехники',
                'Переехали в 203-й: там больше розеток и наконец-то есть где разложить конструктор.',
                "Занятия по робототехнике теперь в 203-м кабинете.\n\nПричина простая: в старом было четыре розетки на группу из шести человек, и половина занятия уходила на очередь к зарядке.", 21],
        ];

        foreach ($rows as $index => [$slug, $title, $excerpt, $content, $daysAgo]) {
            Post::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $index === 1 ? $teacher->id : $admin->id,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'published_at' => now()->subDays($daysAgo),
                ],
            );
        }
    }
}
