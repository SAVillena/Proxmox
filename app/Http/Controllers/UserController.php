<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    //
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
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'username' => 'required|string|max:255',
        ]);

        $user = new User();
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = bcrypt($validatedData['password']);
        $user->username = $validatedData['username'];
        $user->save();
        
        return redirect()->route('users.show', $user->id)->with('success', 'Usuario creado correctamente');
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
