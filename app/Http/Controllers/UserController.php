<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Rules\RutValido;

class UserController extends Controller
{
    // Método para mostrar la lista de usuarios
    public function index()
    {
        return Inertia::render('User/Index');
    }

    // Método para proporcionar los datos de los usuarios en formato DataTables
    public function table()
    {
        return DataTables::of(
            User::with('roles')
                ->select('id', 'rut', 'name', 'email', 'created_at')
        )->make(true);
    }

    // Método para mostrar el formulario de creación
    public function create()
    {
        return Inertia::render('User/Form', [
            'roles'  => Role::all(),
        ]);
    }

    // Método para mostrar el formulario de edición
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return Inertia::render('User/Form', [
            'user'   => $user,
            'roles'  => Role::all(),
        ]);
    }

    // Método para manejar la creación de un nuevo usuario
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->syncRoles($validated['roles']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente');
    }

    // Método para manejar la actualización de un usuario existente
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if ($request->boolean('updatePassword') && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $user->syncRoles($validated['roles']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    // Método para manejar la eliminación de un usuario
    public function destroy(User $user)
    {
        $timestamp = now()->format('YmdHis');

        $user->update([
            'rut'   => $user->rut . '-d-' . $timestamp,
            'email' => $user->email . '-d-' . $timestamp,
        ]);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente');
    }

}
