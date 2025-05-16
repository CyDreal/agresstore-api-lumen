<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    function register(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        // if ($request->fails()) {
        //     return response()->json($request->errors(), 422);
        // }
        $data = $request->only(['username', 'email', 'password']);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Cek jika user tidak ditemukan atau password salah
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Jika berhasil login
        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'address' => $user->address,
                'city' => $user->city,
                'province' => $user->province,
                'phone' => $user->phone,
                'postal_code' => $user->postal_code
            ]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->validate($request, [
            'username' => 'nullable|required',
            // 'email' => 'nullable|required|email|unique:users,email,' . $id,
            // 'password' => 'nullable|required|min:6',
            // 'avatar' => 'nullable|image',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'province' => 'nullable|string',
            'phone' => 'nullable|string',
            'postal_code' => 'nullable|integer'
        ]);

        $user->update($request->all());
        return response()->json(['message' => 'User updated successfully'], 200);
    }

    // public function updateAvatar(Request $request, $id)
    // {
    //
    // }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // if ($user->avatar && file_exists($user->avatar)) {
        //     unlink($user->avatar);
        // }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user], 200);
    }

    public function index()
    {
        $user = User::all();
        return response()->json(['users' => $user], 200);
    }
}
