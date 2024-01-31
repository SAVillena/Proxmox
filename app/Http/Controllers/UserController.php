<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage users');
   }
    public function index()
    {
        $users = User::all();
        return view('users.index', ['users' => $users]);
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('users.show', ['user' => $user]);
    }

    public function create()
    {
        $RolName = Role::all();
        return view('users.create', ['roles' => $RolName]);
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $user = User::create ([ 
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => bcrypt($request['password']),
                'username' => $request['username'],
            ]);

            $user->assignRole($request['role']);

            return redirect()->route('users.show', $user->id)->with('success', 'Usuario creado correctamente');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->route('users.create')->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = User::find($id);
        return view('users.edit', ['user' => $user]);
    }


    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        $user = User::find($id);

        if ($user) {
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];

            if ($validatedData['password']) {
                $user->password = bcrypt($validatedData['password']);
            }

            $user->save();

            return redirect()->route('users.show', $user->id)->with('success', 'Usuario actualizado correctamente');
        } else {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado');
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();
            return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente');
        } else {
            return redirect()->route('users.index')->with('error', 'Usuario no encontrado');
        }
    }
}
