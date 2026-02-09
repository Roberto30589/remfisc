<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

use App\Models\User;
use App\Models\Shift;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /* =========================
     * VISTAS
     * ========================= */

    public function index()
    {
        return Inertia::render('Users/Index');
    }

    public function add()
    {
        return Inertia::render('Users/Form', [
            'roles'  => Role::all(),
            'shifts' => Shift::all(),
        ]);
    }

    public function select(User $user)
    {
        return Inertia::render('Users/Form', [
            'user'   => $user->load('roles', 'shifts'),
            'roles'  => Role::all(),
            'shifts' => Shift::all(),
        ]);
    }

    /* =========================
     * DATATABLE
     * ========================= */

    public function table()
    {
        return DataTables::of(
            User::with('roles', 'shifts')
                ->select('id', 'rut', 'name', 'email', 'created_at')
        )->make(true);
    }

    /* =========================
     * STORE
     * ========================= */

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $user->syncRoles($validated['roles']);
        $user->shifts()->sync($validated['shifts'] ?? []);

        return redirect()
            ->route('users')
            ->with('success', 'Usuario creado correctamente');
    }

    /* =========================
     * UPDATE
     * ========================= */

    public function update(Request $request, User $user)
    {
        $validated = $this->validateUser($request, $user->id);

        if ($request->boolean('updatePassword')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $user->syncRoles($validated['roles']);
        $user->shifts()->sync($validated['shifts'] ?? []);

        return redirect()
            ->route('users')
            ->with('success', 'Usuario actualizado correctamente');
    }

    /* =========================
     * DELETE (SoftDelete)
     * ========================= */

    public function delete(User $user)
    {
        $user->delete();

        return redirect()
            ->route('users')
            ->with('success', 'Usuario eliminado correctamente');
    }

    /* =========================
     * VALIDACIÓN CENTRALIZADA
     * ========================= */

    private function validateUser(Request $request, ?int $userId = null): array
    {
        return $request->validate(
            [
                'rut'      => ['required', 'string', 'max:12', 'unique:users,rut,' . $userId],
                'name'     => ['required', 'string', 'min:6', 'max:255'],
                'email'    => ['nullable', 'email', 'max:255', 'unique:users,email,' . $userId],

                'password' => $userId
                    ? ['nullable', 'confirmed', 'min:8']
                    : ['required', 'confirmed', 'min:8'],

                'roles'    => ['required', 'array', 'min:1'],
                'roles.*'  => ['exists:roles,id'],

                'shifts'   => ['nullable', 'array'],
                'shifts.*' => ['exists:shifts,id'],
            ],
            [
                'required'  => 'El campo :attribute es obligatorio.',
                'string'    => 'El campo :attribute debe ser texto.',
                'min'       => 'El campo :attribute debe tener al menos :min caracteres.',
                'max'       => 'El campo :attribute no debe superar los :max caracteres.',
                'email'     => 'El correo electrónico no es válido.',
                'unique'    => 'El :attribute ya está en uso.',
                'confirmed' => 'La confirmación de contraseña no coincide.',
            ],
            [
                'rut'      => 'RUT',
                'name'     => 'nombre',
                'email'    => 'correo electrónico',
                'password' => 'contraseña',
                'roles'    => 'roles',
            ]
        );
    }
}
