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
        return response()->json([
            'status' => 1,
            'message' => 'Registration successful'
        ]);
    }

    function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek jika user tidak ditemukan atau password salah
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid email or password'
            ]);
        }

        // Jika berhasil login
        return response()->json([
            'status' => 1,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'address' => $user->address,
                'city' => $user->city,
                'province' => $user->province,
                'phone' => $user->phone,
                'postal_code' => $user->postal_code
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'User not found'
            ]);
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
        return response()->json([
            'statuus' => 1,
            'message' => 'User updated successfully',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'address' => $user->address,
                'city' => $user->city,
                'province' => $user->province,
                'phone' => $user->phone,
                'postal_code' => $user->postal_code
            ]
        ]);
    }

    // public function updateAvatar(Request $request, $id)
    // {
    //
    // }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'User not found'
            ]);
        }

        // if ($user->avatar && file_exists($user->avatar)) {
        //     unlink($user->avatar);
        // }

        $user->delete();
        return response()->json([
            'status' => 1,
             'message' => 'User deleted successfully'
        ]);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'User not found'
            ]);
        }

        return response()->json([
            'status' => 1,
            'user' => $user]
        );
    }

    public function index()
    {
        $users = User::all();
        return response()->json([
            'status' => 1,
            'user' => $users
        ]);
    }
}
