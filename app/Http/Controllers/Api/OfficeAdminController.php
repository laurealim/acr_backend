<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Office;
use App\Models\OfficeAdmin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class OfficeAdminController extends Controller
{
    /**
     * Get the office admin record for current user
     */
    private function getOfficeAdmin(Request $request): ?OfficeAdmin
    {
        return OfficeAdmin::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Verify user is an office admin
     */
    private function requireOfficeAdmin(Request $request): OfficeAdmin
    {
        $admin = $this->getOfficeAdmin($request);

        if (!$admin) {
            throw new Exception('You are not authorized as an Office Admin.');
        }

        return $admin;
    }

    /**
     * Get dossier keepers for the office
     */
    public function getDossierKeepers(Request $request): JsonResponse
    {
        try {
            // System admin can see all, office admin sees their office only
            if ($request->user()->hasRole('admin')) {
                $query = Employee::dossierKeepers()->with('office');

                if ($request->has('office_id')) {
                    $query->where('office_id', $request->office_id);
                }

                $keepers = $query->orderBy('name_bangla')->get();
            } else {
                $admin = $this->requireOfficeAdmin($request);

                if (!$admin->can_assign_dossier) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to manage dossier keepers.',
                    ], 403);
                }

                $keepers = Employee::dossierKeepers()
                    ->where('office_id', $admin->office_id)
                    ->with('office')
                    ->orderBy('name_bangla')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $keepers,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Assign dossier keeper role to an employee
     */
    public function assignDossierKeeper(Request $request, $employeeId): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($employeeId);

            // Check authorization
            if ($request->user()->hasRole('admin')) {
                // Admin can assign anyone
            } else {
                $admin = $this->requireOfficeAdmin($request);

                if (!$admin->can_assign_dossier) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to assign dossier keepers.',
                    ], 403);
                }

                // Office admin can only assign within their office
                if ($employee->office_id !== $admin->office_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only assign dossier keepers within your office.',
                    ], 403);
                }
            }

            // Check if employee is already a dossier keeper
            if ($employee->is_dossier_keeper) {
                return response()->json([
                    'success' => false,
                    'message' => 'This employee is already a dossier keeper.',
                ], 422);
            }

            // Check if employee is active
            if (!$employee->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot assign dossier keeper role to inactive employee.',
                ], 422);
            }

            $employee->is_dossier_keeper = true;
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Dossier keeper role assigned successfully',
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
     * Revoke dossier keeper role from an employee
     */
    public function revokeDossierKeeper(Request $request, $employeeId): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($employeeId);

            // Check authorization
            if ($request->user()->hasRole('admin')) {
                // Admin can revoke from anyone
            } else {
                $admin = $this->requireOfficeAdmin($request);

                if (!$admin->can_assign_dossier) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to manage dossier keepers.',
                    ], 403);
                }

                // Office admin can only manage within their office
                if ($employee->office_id !== $admin->office_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only manage dossier keepers within your office.',
                    ], 403);
                }
            }

            // Check if employee is a dossier keeper
            if (!$employee->is_dossier_keeper) {
                return response()->json([
                    'success' => false,
                    'message' => 'This employee is not a dossier keeper.',
                ], 422);
            }

            $employee->is_dossier_keeper = false;
            $employee->save();

            return response()->json([
                'success' => true,
                'message' => 'Dossier keeper role revoked successfully',
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
     * Get employees in office admin's office
     */
    public function getOfficeEmployees(Request $request): JsonResponse
    {
        try {
            if ($request->user()->hasRole('admin')) {
                // Admin can see all employees
                $query = Employee::with('office');

                if ($request->has('office_id')) {
                    $query->where('office_id', $request->office_id);
                }
            } else {
                $admin = $this->requireOfficeAdmin($request);

                if (!$admin->can_manage_employees) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to manage employees.',
                    ], 403);
                }

                $query = Employee::where('office_id', $admin->office_id)->with('office');
            }

            // Filter by grade
            if ($request->has('grade')) {
                $query->where('grade', $request->grade);
            }

            // Filter first class only
            if ($request->boolean('first_class_only')) {
                $query->firstClass();
            }

            // Search
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

            $employees = $query->orderBy('grade')->orderBy('name_bangla')->paginate($request->get('per_page', 20));

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
}
