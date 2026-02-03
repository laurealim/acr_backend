<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class OfficeController extends Controller
{
    /**
     * Get validation rules for office
     */
    private function getValidationRules(bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return [
            'name_bangla' => [$required, 'string', 'max:255'],
            'name_english' => [$required, 'string', 'max:255'],
            'code' => [$required, 'string', 'max:50'],
            'type' => [$required, 'in:ministry,division,department,office'],
            'parent_id' => ['nullable', 'exists:offices,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * List all offices (with filters)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Office::with('parent');

            // Filter by type
            if ($request->has('type')) {
                $query->ofType($request->type);
            }

            // Filter by parent
            if ($request->has('parent_id')) {
                $query->where('parent_id', $request->parent_id);
            }

            // Get only root offices (ministries)
            if ($request->boolean('root_only')) {
                $query->whereNull('parent_id');
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name_bangla', 'like', "%{$search}%")
                        ->orWhere('name_english', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            }

            // Active only (default)
            if (!$request->has('include_inactive')) {
                $query->active();
            }

            $offices = $query->orderBy('type')->orderBy('name_bangla')->get();

            return response()->json([
                'success' => true,
                'data' => $offices,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get office hierarchy tree
     */
    public function hierarchy(Request $request): JsonResponse
    {
        try {
            // Get all ministries with their children
            $hierarchy = Office::with('descendants')
                ->whereNull('parent_id')
                ->active()
                ->orderBy('name_bangla')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $hierarchy,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a new office (admin only)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can create offices.',
                ], 403);
            }

            $rules = $this->getValidationRules();
            $rules['code'][] = 'unique:offices,code';

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Validate parent office type hierarchy
            $data = $validator->validated();
            if (isset($data['parent_id'])) {
                $parent = Office::find($data['parent_id']);
                $typeHierarchy = ['ministry' => 0, 'division' => 1, 'department' => 2, 'office' => 3];

                if ($typeHierarchy[$data['type']] <= $typeHierarchy[$parent->type]) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid office hierarchy. Child office type must be lower than parent.',
                    ], 422);
                }
            }

            $office = Office::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Office created successfully',
                'data' => $office->load('parent'),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show a specific office
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $office = Office::with(['parent', 'children'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $office,
                'meta' => [
                    'hierarchy_path' => $office->hierarchy_path,
                    'employee_count' => $office->employees()->count(),
                    'first_class_count' => $office->firstClassOfficers()->count(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Office not found.',
            ], 404);
        }
    }

    /**
     * Update an office (admin only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can update offices.',
                ], 403);
            }

            $office = Office::findOrFail($id);

            $rules = $this->getValidationRules(true);
            $rules['code'] = ['sometimes', 'string', 'max:50', "unique:offices,code,{$id}"];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            // Prevent setting parent to self or child
            if (isset($data['parent_id'])) {
                if ($data['parent_id'] == $id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Office cannot be its own parent.',
                    ], 422);
                }
            }

            $office->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Office updated successfully',
                'data' => $office->fresh(['parent', 'children']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete an office (admin only)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can delete offices.',
                ], 403);
            }

            $office = Office::findOrFail($id);

            // Check for child offices
            if ($office->children()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete office with child offices. Delete child offices first.',
                ], 422);
            }

            // Check for employees
            if ($office->employees()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete office with employees. Reassign employees first.',
                ], 422);
            }

            $office->delete();

            return response()->json([
                'success' => true,
                'message' => 'Office deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get employees in an office
     */
    public function employees(Request $request, $id): JsonResponse
    {
        try {
            $office = Office::findOrFail($id);

            $query = $office->employees()->with('user');

            // Filter by grade
            if ($request->has('grade')) {
                $query->where('grade', $request->grade);
            }

            // Filter first class only
            if ($request->boolean('first_class_only')) {
                $query->firstClass();
            }

            // Active only
            if (!$request->has('include_inactive')) {
                $query->active();
            }

            $employees = $query->orderBy('grade')->orderBy('name_bangla')->get();

            return response()->json([
                'success' => true,
                'data' => $employees,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Office not found.',
            ], 404);
        }
    }

    /**
     * Get first class officers in an office (for IO/CO selection)
     */
    public function firstClassOfficers(Request $request, $id): JsonResponse
    {
        try {
            $office = Office::findOrFail($id);

            $officers = $office->firstClassOfficers()
                ->with('user')
                ->orderBy('grade')
                ->orderBy('name_bangla')
                ->get(['id', 'employee_id', 'name_bangla', 'name_english', 'designation', 'grade']);

            return response()->json([
                'success' => true,
                'data' => $officers,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Office not found.',
            ], 404);
        }
    }
}
