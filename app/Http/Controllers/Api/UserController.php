<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class UserController extends Controller
{
    /**
     * Get validation rules for user profile (employee data)
     */
    private function getEmployeeValidationRules(bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return [
            // Personal Information
            'name_bangla' => [$required, 'string', 'max:255'],
            'date_of_birth' => [$required, 'date', 'date_format:Y-m-d'],
            'father_name' => [$required, 'string', 'max:255'],
            'mother_name' => [$required, 'string', 'max:255'],
            'nid_number' => ['nullable', 'string', 'max:20', 'unique:employees,nid_number'],
            'gender' => [$required, 'in:male,female,other'],
            'marital_status' => [$required, 'in:single,married,divorced,widowed'],
            'number_of_children' => ['nullable', 'integer', 'min:0', 'max:99'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'personal_phone' => ['nullable', 'string', 'max:20', 'unique:employees,personal_phone'],
            'permanent_address' => ['nullable', 'string', 'max:500'],
            'present_address' => ['nullable', 'string', 'max:500'],

            // Employment Information
            'office_id' => [$required, 'exists:offices,id'],
            'employee_id' => [$required, 'string', 'max:50', $isUpdate ? 'sometimes' : 'unique:employees,employee_id'],
            'grade' => [$required, 'integer', 'between:1,20'],
            'employee_class' => [$required, 'in:1st_class,2nd_class,3rd_class,4th_class'],
            'designation' => [$required, 'string', 'max:255'],
            'cadre' => ['nullable', 'string', 'max:100'],
            'batch' => ['nullable', 'string', 'max:50'],
            'govt_service_join_date' => [$required, 'date', 'date_format:Y-m-d'],
            'gazetted_post_join_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'cadre_join_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'current_position_join_date' => [$required, 'date', 'date_format:Y-m-d'],
            'prl_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'highest_education' => [$required, 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:5120'], // 5MB max
            'is_dossier_keeper' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'suspension_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get validation rules for user credentials
     */
    private function getUserValidationRules(bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';
        $emailUnique = $isUpdate ? 'unique:users,email' : 'unique:users,email';

        return [
            'name' => [$required, 'string', 'max:255'],
            'email' => [$required, 'email', 'max:255', $emailUnique],
            'password' => [$isUpdate ? 'sometimes' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * List all users with their employee profiles
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with('employees', 'roles');

            // Search by name or email
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->has('role')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            $users = $query->orderBy('name')->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $users,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী তালিকা পুনরুদ্ধার ব্যর্থ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user's profile with employee data
     */
    public function myProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load(['employees', 'roles', 'adminOffices']);

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'প্রোফাইল পুনরুদ্ধার ব্যর্থ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update authenticated user's profile
     */
    public function updateMyProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $employee = $user->employees;

            // Validate user credentials
            $userValidator = Validator::make($request->all(), [
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            ]);

            if ($userValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $userValidator->errors(),
                ], 422);
            }

            // Validate employee data
            $employeeValidator = Validator::make($request->all(), $this->getEmployeeValidationRules(true));

            if ($employeeValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $employeeValidator->errors(),
                ], 422);
            }

            return DB::transaction(function () use ($request, $user, $employee) {
                // Update user credentials
                if ($request->has('name')) {
                    $user->name = $request->name;
                }

                if ($request->has('email')) {
                    $user->email = $request->email;
                    $user->email_verified_at = null; // Reset email verification
                }

                if ($request->has('password')) {
                    $user->password = Hash::make($request->password);
                }

                $user->save();

                // Update employee data if employee exists
                if ($employee) {
                    $employeeData = $request->only([
                        'name_bangla', 'date_of_birth', 'father_name', 'mother_name',
                        'nid_number', 'gender', 'marital_status', 'number_of_children',
                        'blood_group', 'personal_phone', 'permanent_address', 'present_address',
                        'office_id', 'employee_id', 'grade', 'employee_class', 'designation',
                        'cadre', 'batch', 'govt_service_join_date', 'gazetted_post_join_date',
                        'cadre_join_date', 'current_position_join_date', 'prl_date',
                        'highest_education', 'is_dossier_keeper', 'is_active', 'suspension_reason'
                    ]);

                    // Handle photo upload
                    if ($request->hasFile('photo')) {
                        $photoPath = $request->file('photo')->store('employees/photos', 'public');
                        $employeeData['photo'] = $photoPath;
                    }

                    // Auto-calculate employee_class from grade if grade is provided
                    if (isset($employeeData['grade'])) {
                        $employeeData['employee_class'] = $this->getEmployeeClassFromGrade($employeeData['grade']);
                    }

                    // Set suspended_at timestamp if is_active changed
                    if (isset($employeeData['is_active'])) {
                        if (!$employeeData['is_active']) {
                            $employeeData['suspended_at'] = now();
                        } else {
                            $employeeData['suspended_at'] = null;
                        }
                    }

                    $employee->update($employeeData);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'প্রোফাইল সফলভাবে আপডেট হয়েছে',
                    'data' => $user->load(['employees', 'roles']),
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'প্রোফাইল আপডেট ব্যর্থ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific user with employee profile
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::with('employees', 'roles', 'adminOffices')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী পাওয়া যায়নি: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create a new user (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        // Validate user credentials
        $userValidator = Validator::make($request->all(), $this->getUserValidationRules(false));

        if ($userValidator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $userValidator->errors(),
            ], 422);
        }

        // Validate employee data
        $employeeValidator = Validator::make($request->all(), $this->getEmployeeValidationRules(false));

        if ($employeeValidator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $employeeValidator->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                // Create user
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // Assign default user role
                $user->assignRole('user');

                // Create employee profile
                $employeeData = $request->only([
                    'office_id', 'employee_id', 'name_bangla', 'date_of_birth',
                    'father_name', 'mother_name', 'nid_number', 'gender',
                    'marital_status', 'number_of_children', 'blood_group',
                    'personal_phone', 'permanent_address', 'present_address',
                    'grade', 'employee_class', 'designation', 'cadre', 'batch',
                    'govt_service_join_date', 'gazetted_post_join_date',
                    'cadre_join_date', 'current_position_join_date', 'prl_date',
                    'highest_education', 'is_dossier_keeper', 'is_active'
                ]);

                $employeeData['user_id'] = $user->id;
                $employeeData['personal_email'] = $request->email;

                // Handle photo upload
                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('employees/photos', 'public');
                    $employeeData['photo'] = $photoPath;
                }

                // Auto-calculate employee_class from grade
                $employeeData['employee_class'] = $this->getEmployeeClassFromGrade($employeeData['grade']);

                $employee = Employee::create($employeeData);

                return response()->json([
                    'success' => true,
                    'message' => 'ব্যবহারকারী সফলভাবে তৈরি হয়েছে',
                    'data' => [
                        'user' => $user->load(['employees', 'roles']),
                        'employee' => $employee,
                    ],
                ], 201);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী তৈরি ব্যর্থ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a user (admin only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $employee = $user->employees;

            // Validate user credentials
            $userValidator = Validator::make($request->all(), [
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            ]);

            if ($userValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $userValidator->errors(),
                ], 422);
            }

            // Validate employee data
            $employeeValidator = Validator::make($request->all(), $this->getEmployeeValidationRules(true));

            if ($employeeValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $employeeValidator->errors(),
                ], 422);
            }

            return DB::transaction(function () use ($request, $user, $employee) {
                // Update user credentials
                if ($request->has('name')) {
                    $user->name = $request->name;
                }

                if ($request->has('email')) {
                    $user->email = $request->email;
                }

                if ($request->has('password')) {
                    $user->password = Hash::make($request->password);
                }

                $user->save();

                // Update employee data if exists
                if ($employee) {
                    $employeeData = $request->only([
                        'name_bangla', 'date_of_birth', 'father_name', 'mother_name',
                        'nid_number', 'gender', 'marital_status', 'number_of_children',
                        'blood_group', 'personal_phone', 'permanent_address', 'present_address',
                        'office_id', 'employee_id', 'grade', 'employee_class', 'designation',
                        'cadre', 'batch', 'govt_service_join_date', 'gazetted_post_join_date',
                        'cadre_join_date', 'current_position_join_date', 'prl_date',
                        'highest_education', 'is_dossier_keeper', 'is_active', 'suspension_reason'
                    ]);

                    // Handle photo upload
                    if ($request->hasFile('photo')) {
                        $photoPath = $request->file('photo')->store('employees/photos', 'public');
                        $employeeData['photo'] = $photoPath;
                    }

                    // Auto-calculate employee_class from grade
                    if (isset($employeeData['grade'])) {
                        $employeeData['employee_class'] = $this->getEmployeeClassFromGrade($employeeData['grade']);
                    }

                    // Set suspended_at timestamp if is_active changed
                    if (isset($employeeData['is_active'])) {
                        if (!$employeeData['is_active']) {
                            $employeeData['suspended_at'] = now();
                        } else {
                            $employeeData['suspended_at'] = null;
                        }
                    }

                    $employee->update($employeeData);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'ব্যবহারকারী সফলভাবে আপডেট হয়েছে',
                    'data' => $user->load(['employees', 'roles']),
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী আপডেট ব্যর্থ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user (admin only)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting own account
            if (auth()->id() === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'আপনি আপনার নিজের অ্যাকাউন্ট মুছতে পারবেন না',
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'ব্যবহারকারী সফলভাবে মুছে ফেলা হয়েছে',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ব্যবহারকারী মুছতে ব্যর্থ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to get employee class from grade
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
}
