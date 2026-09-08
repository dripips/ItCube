<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(): View
    {
        return view('admin.groups', [
            'groups' => Group::with(['direction', 'teacher', 'schedules'])
                ->withCount('students')
                ->orderBy('is_archived')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.group-form', $this->formData(new Group(['is_archived' => false])));
    }

    public function edit(Group $group): View
    {
        return view('admin.group-form', $this->formData($group->load(['schedules', 'students'])));
    }

    public function store(Request $request): RedirectResponse
    {
        $group = Group::create($this->validated($request, null));
        $this->syncSchedules($request, $group);
        $group->students()->sync($request->input('students', []));

        return redirect()->route('admin.groups.edit', $group)->with('status', __('Группа заведена'));
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        $group->update($this->validated($request, $group));
        $this->syncSchedules($request, $group);
        $group->students()->sync($request->input('students', []));

        return back()->with('status', __('Изменения сохранены'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Group $group): array
    {
        return [
            'group' => $group,
            'directions' => Direction::orderBy('name')->get(),
            'teachers' => User::whereIn('role', [Role::Teacher, Role::Admin])->orderBy('last_name')->get(),
            'students' => User::where('role', Role::Student)->orderBy('last_name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Group $group): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('groups')->ignore($group?->id)],
            'direction_id' => ['required', 'exists:directions,id'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ]);

        // Поля slug в форме нет: валидатор не кладёт в результат ключ, которого
        // не было во входе, и обращение к нему напрямую роняло любое сохранение.
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        $data['is_archived'] = $request->boolean('is_archived');

        return $data;
    }

    private function syncSchedules(Request $request, Group $group): void
    {
        $slots = collect($request->input('schedule', []))
            ->filter(fn ($slot): bool => filled($slot['starts_at'] ?? null) && filled($slot['ends_at'] ?? null));

        // Расписание переписывается целиком: сравнивать построчно нечего,
        // строки не имеют собственного смысла за пределами группы.
        $group->schedules()->delete();

        foreach ($slots as $slot) {
            $group->schedules()->create([
                'day_of_week' => (int) $slot['day_of_week'],
                'starts_at' => $slot['starts_at'],
                'ends_at' => $slot['ends_at'],
                'room' => $slot['room'] ?? null,
            ]);
        }
    }
}
