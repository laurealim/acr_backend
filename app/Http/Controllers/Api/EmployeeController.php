<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class EmployeeController extends Controller
{
    /**
     * Get validation rules for employee
     */
    private function getValidationRules(bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return [
            'office_id' => [$required, 'exists:offices,id'],
            'employee_id' => [$required, 'string', 'max:50', $isUpdate ? 'sometimes' : 'unique:employees,employee_id'],
            'name_bangla' => [$required, 'string', 'max:255'],
            'name_english' => [$required, 'string', 'max:255'],
            'nid_number' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => [$required, 'date'],
            'father_name' => [$required, 'string', 'max:255'],
            'mother_name' => [$required, 'string', 'max:255'],
            'gender' => [$required, 'in:male,female,other'],
            'marital_status' => [$required, 'in:single,married,divorced,widowed'],
            'number_of_children' => ['nullable', 'integer', 'min:0'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'personal_phone' => ['nullable', 'string', 'max:20'],
            'permanent_address' => ['nullable', 'string'],
            'present_address' => ['nullable', 'string'],
            'grade' => [$required, 'integer', 'between:1,20'],
            'designation' => [$required, 'string', 'max:255'],
            'cadre' => ['nullable', 'string', 'max:100'],
            'batch' => ['nullable', 'string', 'max:50'],
            'govt_service_join_date' => [$required, 'date'],
            'gazetted_post_join_date' => ['nullable', 'date'],
            'cadre_join_date' => ['nullable', 'date'],
            'current_position_join_date' => [$required, 'date'],
            'prl_date' => ['nullable', 'date'],
            'highest_education' => [$required, 'string', 'max:255'],
        ];
    }

    /**
     * List all employees (with filters)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Employee::with('office');

            // Filter by office
            if ($request->has('office_id')) {
                $query->where('office_id', $request->office_id);
            }

            // Filter by grade
            if ($request->has('grade')) {
                $query->where('grade', $request->grade);
            }

            // Filter by class
            if ($request->has('employee_class')) {
                $query->where('employee_class', $request->employee_class);
            }

            // Filter first class officers only
            if ($request->boolean('first_class_only')) {
                $query->firstClass();
            }

            // Filter dossier keepers
            if ($request->boolean('dossier_keepers_only')) {
                $query->dossierKeepers();
            }

            // Search by name
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name_bangla', 'like', "%{$search}%")
                        ->orWhere('name_english', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }

            // Active only (default)
            if (!$request->has('include_inactive')) {
                $query->active();
            }

            $employees = $query->orderBy('name_bangla')->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $employees,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a new employee
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Only admin can create employees
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can create employee profiles.',
                ], 403);
            }

            $validator = Validator::make($request->all(), array_merge(
                $this->getValidationRules(),
                ['user_id' => ['required', 'exists:users,id', 'unique:employees,user_id']]
            ));

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            $data['employee_class'] = Employee::calculateClass($data['grade']);

            $employee = Employee::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => $employee->load('office'),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get current user's employee profile
     */
    public function myProfile(Request $request): JsonResponse
    {
        try {
            $employee = $request->user()->employee;

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee profile not found. Please contact your administrator.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $employee->load('office'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update current user's employee profile
     */
    public function updateMyProfile(Request $request): JsonResponse
    {
        try {
            $employee = $request->user()->employee;

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee profile not found.',
                ], 404);
            }

            // Limited fields that employee can update themselves
            $validator = Validator::make($request->all(), [
                'personal_email' => ['sometimes', 'email', 'max:255'],
                'personal_phone' => ['sometimes', 'string', 'max:20'],
                'present_address' => ['sometimes', 'string'],
                'blood_group' => ['sometimes', 'string', 'max:10'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $employee->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $employee->fresh('office'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show a specific employee
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $employee = Employee::with(['office', 'user'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $employee,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        }
    }

    /**
     * Update an employee (admin only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can update employee profiles.',
                ], 403);
            }

            $employee = Employee::findOrFail($id);

            $rules = $this->getValidationRules(true);
            // Make employee_id unique except for current record
            $rules['employee_id'] = ['sometimes', 'string', 'max:50', "unique:employees,employee_id,{$id}"];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            // Recalculate employee class if grade changed
            if (isset($data['grade'])) {
                $data['employee_class'] = Employee::calculateClass($data['grade']);
            }

            $employee->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully',
                'data' => $employee->fresh('office'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete an employee (admin only)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can delete employee profiles.',
                ], 403);
            }

            $employee = Employee::findOrFail($id);

            // Check if employee has any ACRs
            if ($employee->acrs()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete employee with existing ACR records.',
                ], 422);
            }

            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
