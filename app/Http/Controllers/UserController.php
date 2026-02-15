<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use App\Models\Shift;
use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Rules\RutValido;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('User/Index');
    }

    public function add()
    {
        return Inertia::render('User/Form', [
            'roles'  => Role::all(),
            'shifts' => Shift::all(),
        ]);
    }

    public function edit($id)
    {
        $user = User::with('roles', 'shifts')->findOrFail($id);

        return Inertia::render('User/Form', [
            'user'   => $user,
            'roles'  => Role::all(),
            'shifts' => Shift::all(),
        ]);
    }

    public function table()
    {
        return DataTables::of(
            User::with('roles', 'shifts')
                ->select('id', 'rut', 'name', 'email', 'created_at')
        )->make(true);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $user->syncRoles($validated['roles']);
        $user->shifts()->sync($validated['shifts'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validated();

        if ($request->boolean('updatePassword') && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $user->syncRoles($validated['roles']);
        $user->shifts()->sync($validated['shifts'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente');
    }
}
