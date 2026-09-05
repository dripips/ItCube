# ItCube

A platform for a small coding school: tracks, groups, timetable, attendance
register — and coding tasks that check themselves.

Русский: [README.ru.md](README.ru.md)

![Задача с проверкой по наборам данных](docs/screenshots/01-assignment.png)

The first version was written in 2023: procedural PHP, two separate login
flows, attendance stored as a pair of student and date with no group, and file
uploads that took the extension from the filename and dropped the file into a
served directory. This version is a rewrite on Laravel. Only the domain
survived.

## What it does

**Runs student code in fourteen languages.** Python, JavaScript, TypeScript,
PHP, C, C++, Java, C#, Go, Rust, Ruby, Pascal, SQL and Bash. There is no
sandbox here and there never will be: code goes to [Wandbox](https://wandbox.org),
which already runs untrusted code for a living. A school platform has no
business maintaining that infrastructure itself.

One backend rather than several is not a simplification. The Go Playground does
not accept stdin, and without stdin there is no such thing as a test case with
input data — which means no assessment worth the name.

**Checks against several sets of data, not one.** A task carries test cases,
each with its own input, expected output and points. Some are hidden: the
student sees only whether a hidden case passed, never its input or its expected
answer. Otherwise a solution gets fitted to the known answer instead of being
made to work.

**Grades in the background.** Submitting queues the work and the page polls for
the result. That is not architecture for its own sake — measured: five test
cases in Go take about a minute, because one build on the playground runs for
roughly twenty seconds. Cases are sent in parallel; a synchronous request would
have to hold the connection open the whole time.

**Quizzes with a timer** that submits the answers on its own when time runs
out, and **assessments** that combine quizzes and coding tasks into one graded
paper with an open window.

**An attendance register** that records the group, so a student enrolled in two
tracks is no longer indistinguishable on the same day — the 2023 schema could
not tell those apart.

**Three languages.** Russian, English and German, in the gettext style: the
translation key is the source string itself, so anything untranslated stays
readable text rather than a key name in the middle of the page. Adding a
language means translating one JSON file. `php artisan lang:check` compares the
dictionaries against the code, and a test keeps them from drifting apart.

## Screenshots

| | |
|---|---|
| ![Журнал посещаемости](docs/screenshots/02-journal.png) Attendance register | ![Ведомость](docs/screenshots/06-sheet.png) Assessment sheet |
| ![Тест с таймером](docs/screenshots/03-quiz.png) Quiz with a timer | ![Занятие](docs/screenshots/04-lesson.png) A lesson |
| ![Моё обучение](docs/screenshots/05-learn.png) Student's home | ![Тёмная тема](docs/screenshots/01-assignment-dark.png) Dark theme |

## Running it

PHP 8.3+, Composer and PostgreSQL.

```bash
git clone https://github.com/dripips/ItCube.git
cd ItCube
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
createdb itcube
php artisan migrate --seed
php artisan serve
```

The seed builds a demo school: four tracks, five groups, eighteen students,
lessons with real tasks, a quiz, an assessment and a month of attendance.
Everyone's password is `password`; sign in as `lebedeva` for the teacher's
side or `artem` for a student's.

Grading runs on a queue, so start a worker too:

```bash
php artisan queue:work
```

No accounts or keys are needed for any third-party service — Wandbox works
without registration.

## Tests

```bash
php artisan test
```

Fifty-one tests. None of them touch the network: HTTP is faked, so the suite
does not depend on somebody else's uptime.

## What is not here

No file manager yet, and no admin screens for creating tracks and groups —
those are seeded or written by hand for now.

The blocklists in `CodeRunner` are politeness, not isolation. They stop the
obvious nonsense before the network request so that a student gets a clear
answer instead of a timeout. What makes this safe is that the code does not run
on your server.

## Licence

MIT.
