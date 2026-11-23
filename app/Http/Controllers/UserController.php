<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $validated_user = $request->validate([
            'name' => 'required|string|max:256',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $validated_user['password'] = Hash::make($validated_user['password']);
        $user = User::create($validated_user);
        $user->sendEmailVerificationNotification(); 
        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'msg' => 'Please check your email and verify you email!',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // Profile
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    // Login
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'User not found',
            ], 401);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response([
            'message' => 'User Login Successfuly',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    // Verify Email
    public  function verifyEmail($id)
    {
        $user = User::findOrFail($id);

        if (!$user->hasVerifiedEmail()) {
            return $user->markEmailAsVerified();
        }

        return response()->json([
            'message' => 'Email verified successfully',
        ]);
    }

    // Resend Email
    public function resendEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent'
        ]);
    }

    // Logout
    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();

        return response([
            'message' => 'Logout Successfuly',
        ]);
    }

    // GET List 
    public function get()
    {
        $users = User::orderBy('id', 'asc')->get();

        return response()->json([
            'msg' => 'Users Retrieved Successfully',
            'users' => $users,
        ]);
    }

    //Forgot Pasword
    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );


        return response()->json([
            'message' => 'Reset link request processed',
            'status' => $status,
        ]);
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $status = Password::reset(
            $validated,
            function ($user, $password) {

                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successfully',
            ]);
        }

        return response()->json([
            'message' => 'Invalid or expired reset token',
            'status' => $status,
        ], 422);
    }

    // Specific User
    public function specificUser($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'msg' => 'Users Retrieved Successfully',
            'user' => $user,
        ]);
    }

    // UPDATE USER
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $updated_data = $request->validate([
            'name' => 'sometimes|string|max:256',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'sometimes|string|min:8',
        ]);

        if (isset($updated_data['password'])) {
            $updated_data['password'] = Hash::make($updated_data['password']);
        }

        $user->update($updated_data);

        return response()->json([
            'msg' => 'User Updated Successfully',
            'user' => $user->fresh(),
        ]);
    }

    // DELETE USER
    public function delete(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->user()->id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized action',
            ], 403);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'msg' => 'User Deleted Successfully',
        ]);
    }
}
