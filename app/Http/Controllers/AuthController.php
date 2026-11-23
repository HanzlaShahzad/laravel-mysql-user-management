<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
       $validated = $request->validate([
        'name' => 'string|required|max:256',
        'email' => 'string|required|unique:user,email',
        'password' => 'required|confirmed|min:8',
       ]);

       $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
       ]);

       Auth::login($user);

       return response([
        'msg' => 'User Created Successfuly',
        'user' => $user,
       ]);

    }
}
