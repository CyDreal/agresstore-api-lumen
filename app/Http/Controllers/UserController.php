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

    public function updateAvatar(Request $request, $id)
    {
        $this->validate($request, [
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'User not found'
            ], 404);
        }

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();

            // Delete old avatar if exists
            if ($user->avatar) {
                $oldPath = storage_path('app/public/images/' . basename($user->avatar));
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Upload new avatar
            $image->move(storage_path('app/public/images'), $imageName);

            // Update avatar path in database
            $user->update([
                'avatar' => env('APP_URL') . '/storage/images/' . $imageName
            ]);

            return response()->json([
                'status' => 1,
                'message' => 'Avatar updated successfully',
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

        return response()->json([
            'status' => 0,
            'message' => 'No image uploaded'
        ], 400);
    }

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

        return response()->json(
            [
                'status' => 1,
                'user' => $user
            ]
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

    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password'
        ]);

        try {
            $user = User::find($request->user_id);

            // Check old password
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update to new password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'status' => 1,
                'message' => 'Password updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to update password: ' . $e->getMessage()
            ], 500);
        }
    }
}
