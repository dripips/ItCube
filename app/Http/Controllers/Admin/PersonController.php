<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonController extends Controller
{
    public function index(Request $request): View
    {
        $role = Role::tryFrom((string) $request->query('role'));

        return view('admin.people', [
            'role' => $role,
            'people' => User::query()
                ->when($role, fn ($q) => $q->where('role', $role))
                ->when($request->filled('q'), function ($q) use ($request) {
                    $needle = '%'.mb_strtolower($request->string('q')->toString()).'%';
                    $q->where(function ($inner) use ($needle) {
                        $inner->whereRaw('lower(username) like ?', [$needle])
                            ->orWhereRaw('lower(coalesce(last_name, \'\')) like ?', [$needle])
                            ->orWhereRaw('lower(coalesce(first_name, \'\')) like ?', [$needle]);
                    });
                })
                ->withCount('groups')
                ->orderBy('role')
                ->orderBy('last_name')
                ->paginate(25)
                ->withQueryString(),
            'counts' => User::query()->groupBy('role')->selectRaw('role, count(*) as total')->pluck('total', 'role'),
        ]);
    }

    public function create(): View
    {
        return view('admin.person-form', [
            'person' => new User(['role' => Role::Student]),
            'groups' => Group::with('direction')->where('is_archived', false)->orderBy('name')->get(),
            'students' => collect(),
        ]);
    }

    public function edit(User $person): View
    {
        return view('admin.person-form', [
            'person' => $person->load(['groups', 'children']),
            'groups' => Group::with('direction')->where('is_archived', false)->orderBy('name')->get(),
            'students' => User::where('role', Role::Student)->orderBy('last_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        $person = User::create([
            ...$data,
            'name' => trim($data['first_name'].' '.$data['last_name']),
            // Без пароля в форме заводится тот, что раздают на первом занятии.
            'password' => Hash::make($request->string('password')->toString() ?: 'password'),
        ]);

        $this->syncLinks($request, $person);

        return redirect()->route('admin.people.edit', $person)->with('status', __('Человек заведён'));
    }

    public function update(Request $request, User $person): RedirectResponse
    {
        $data = $this->validated($request, $person);

        $person->update([...$data, 'name' => trim($data['first_name'].' '.$data['last_name'])]);

        // Пустое поле пароля означает «не трогать», а не «поставить пустой».
        if ($request->filled('password')) {
            $person->update(['password' => Hash::make($request->string('password')->toString())]);
        }

        $this->syncLinks($request, $person);

        return back()->with('status', __('Изменения сохранены'));
    }

    /**
     * Поля учётной записи без пароля.
     *
     * Пароль сюда не попадает намеренно: пустое поле в форме означает «не
     * трогать», а если бы он ехал в общем массиве, правка человека без ввода
     * пароля затирала бы существующий на null.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $person): array
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:60', Rule::unique('users')->ignore($person?->id)],
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($person?->id)],
            'role' => ['required', Rule::enum(Role::class)],
            'bio' => ['nullable', 'string', 'max:1000'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        unset($data['password']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function syncLinks(Request $request, User $person): void
    {
        if ($person->isStudent()) {
            $person->groups()->sync($request->input('groups', []));
        }

        if ($person->isGuardian()) {
            $person->children()->sync($request->input('children', []));
        }
    }
}
