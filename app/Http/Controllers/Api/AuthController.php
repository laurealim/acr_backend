<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use HasinHayder\Tyro\Models\Role;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // Assign default user role
                $userRole = Role::firstOrCreate(
                    ['slug' => 'user'],
                    ['name' => 'User']
                );
                $user->assignRole($userRole);

                // Get or create default office (the root ministry/first office)
                $defaultOffice = Office::whereIn('type', ['ministry', 'office'])
                    ->where('is_active', true)
                    ->first();

                if (!$defaultOffice) {
                    // Create a default office if none exists
                    $defaultOffice = Office::create([
                        'name_bangla' => 'সাধারণ অফিস',
                        'name_english' => 'General Office',
                        'code' => 'GEN-' . Str::random(6),
                        'type' => 'office',
                        'is_active' => true,
                    ]);
                }

                // Generate unique employee ID
                $employeeId = 'EMP-' . date('Y') . '-' . Str::random(8);
                while (Employee::where('employee_id', $employeeId)->exists()) {
                    $employeeId = 'EMP-' . date('Y') . '-' . Str::random(8);
                }

                // Create employee record with default/dummy data for required fields
                $employee = Employee::create([
                    'user_id' => $user->id,
                    'office_id' => $defaultOffice->id,
                    'employee_id' => $employeeId,
                    'name_bangla' => 'নাম বাংলায়', // Default placeholder
                    'name_english' => $request->name,
                    'date_of_birth' => now()->subYears(30)->format('Y-m-d'), // Default: 30 years ago
                    'father_name' => 'পিতার নাম', // Default placeholder
                    'mother_name' => 'মাতার নাম', // Default placeholder
                    'gender' => 'male', // Default
                    'marital_status' => 'single', // Default
                    'grade' => 15, // Default: general staff
                    'employee_class' => $this->getEmployeeClassFromGrade(15), // Default class
                    'designation' => 'কর্মচারী', // Default: Employee
                    'personal_email' => $request->email,
                    'govt_service_join_date' => now()->format('Y-m-d'),
                    'current_position_join_date' => now()->format('Y-m-d'),
                    'highest_education' => 'মাধ্যমিক', // Default
                    'is_active' => true,
                ]);

                // Create access token
                $accessToken = $user->createToken('api-token')->plainTextToken;

                // Create refresh token (stored in httpOnly cookie)
                $refreshToken = $user->createToken('refresh-token', ['refresh'])->plainTextToken;

                $response = response()->json([
                    'success' => true,
                    'message' => 'ব্যবহারকারী সফলভাবে নিবন্ধিত হয়েছে',
                    'data' => [
                        'user' => $user->load(['roles', 'employees']),
                        'employee' => $employee,
                        'token' => $accessToken,
                    ],
                ], 201);

                // Set httpOnly refresh token cookie
                $response->cookie(
                    'refresh_token',
                    $refreshToken,
                    60 * 24 * 7, // 7 days
                    '/',
                    null, // domain
                    true, // secure (enable in production only)
                    true, // httpOnly
                    false, // raw
                    'lax' // sameSite
                );

                return $response;
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'নিবন্ধন ব্যর্থ হয়েছে: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Check if user is suspended
        if ($user->is_suspended) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.',
            ], 403);
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        // Create access token
        $accessToken = $user->createToken('api-token')->plainTextToken;

        // Create refresh token (stored in httpOnly cookie)
        $refreshToken = $user->createToken('refresh-token', ['refresh'])->plainTextToken;

        // Load roles with their privileges, and employee data
        $user->load(['roles.privileges', 'employees']);

        // Get all privileges from all roles (flattened, unique)
        $privileges = $user->roles
            ->flatMap(fn($role) => $role->privileges)
            ->unique('id')
            ->map(fn($privilege) => [
                'id' => $privilege->id,
                'name' => $privilege->name,
                'slug' => $privilege->slug,
            ])
            ->values()
            ->all();

        $response = response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'privileges' => $privileges,
                'token' => $accessToken,
            ],
        ], 200);

        // Set httpOnly refresh token cookie
        $response->cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7, // 7 days
            '/',
            null, // domain
            true, // secure (enable in production only)
            true, // httpOnly
            false, // raw
            'lax' // sameSite
        );

        return $response;
    }

    /**
     * Get current user with roles and privileges
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['roles.privileges', 'employees']);

        // Get all privileges from all roles (flattened, unique)
        $privileges = $user->roles
            ->flatMap(fn($role) => $role->privileges)
            ->unique('id')
            ->map(fn($privilege) => [
                'id' => $privilege->id,
                'name' => $privilege->name,
                'slug' => $privilege->slug,
            ])
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'privileges' => $privileges,
            ],
        ], 200);
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // Create a password reset token manually
        \DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => \Hash::make(\Str::random(60)),
                'created_at' => now(),
            ]
        );

        // Get the token we just created
        $resetToken = \DB::table('password_resets')
            ->where('email', $user->email)
            ->latest()
            ->first();

        // For API, we'll log the reset link (in production, send via email)
        // Frontend will use: http://localhost:5173/password/reset?token=TOKEN&email=EMAIL
        $resetLink = url("/password/reset?token={$resetToken->token}&email=" . urlencode($user->email));

        // Log for development (check storage/logs/laravel.log)
        \Log::info("Password reset link for {$user->email}: {$resetLink}");

        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to email',
            'data' => [
                'reset_link' => $resetLink, // For dev/testing only
            ]
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['success' => true, 'message' => __($status)]);
        }

        return response()->json(['success' => false, 'message' => __($status)], 400);
    }

    /**
     * Resend email verification link
     */
    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['success' => false, 'message' => 'Email already verified'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['success' => true, 'message' => 'Verification link sent']);
    }

    /**
     * Verify email (signed URL)
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['success' => false, 'message' => 'Invalid verification link'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['success' => true, 'message' => 'Email already verified']);
        }

        $user->markEmailAsVerified();

        return response()->json(['success' => true, 'message' => 'Email verified successfully']);
    }

    /**
     * Helper method to get employee class from grade
     * Grade 1-9: 1st Class Officers
     * Grade 10-13: 2nd Class Officers
     * Grade 14-16: 3rd Class Staff
     * Grade 17-20: 4th Class Staff
     */
    private function getEmployeeClassFromGrade(int $grade): string
    {
        if ($grade >= 1 && $grade <= 9) {
            return '1st_class';
        } elseif ($grade >= 10 && $grade <= 13) {
            return '2nd_class';
        } elseif ($grade >= 14 && $grade <= 16) {
            return '3rd_class';
        } else {
            return '4th_class';
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Refresh token
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();

        // Revoke old access token
        $request->user()->currentAccessToken()->delete();

        // Create new access token
        $accessToken = $user->createToken('api-token')->plainTextToken;

        // Create new refresh token
        $refreshToken = $user->createToken('refresh-token', ['refresh'])->plainTextToken;

        $response = response()->json([
            'success' => true,
            'message' => 'Token refreshed',
            'data' => [
                'token' => $accessToken,
            ],
        ], 200);

        // Set new httpOnly refresh token cookie
        $response->cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 7, // 7 days
            '/',
            null, // domain
            true, // secure (enable in production only)
            true, // httpOnly
            false, // raw
            'lax' // sameSite
        );

        return $response;
    }
}
