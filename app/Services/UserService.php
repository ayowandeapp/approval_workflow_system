<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


final class UserService
{

    public function createUser($request)
    {
        return User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id
        ]);
    }

    public function getUsers(int $length = 10)
    {
        return User::with('department')->paginate($length);
    }

    public function updateUser(Request $request, User $user)
    {

        $data = $request->only(['username', 'department_id']);

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return $user;
    }

}
