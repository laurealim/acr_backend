<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ACR;
use App\Models\Employee;
use App\Models\AcrPdf;
use App\Services\ACRWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;

class ACRController extends Controller
{
    protected ACRWorkflowService $workflowService;

    public function __construct(ACRWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Get validation rules for employee-editable fields
     */
    private function getEmployeeValidationRules(bool $isUpdate = false): array
    {
        $required = $isUpdate ? 'sometimes' : 'required';

        return [
            // Basic Info
            'reporting_year' => [$required, 'string', 'max:50'],
            'name_bangla' => [$required, 'string', 'max:255'],
            'name_english' => [$required, 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:50'],
            'batch' => ['nullable', 'string', 'max:50'],
            'cadre' => ['nullable', 'string', 'max:100'],
            'nid_number' => ['nullable', 'string', 'max:20'],

            // Position Info
            'designation_during_period' => [$required, 'string', 'max:255'],
            'workplace_during_period' => [$required, 'string', 'max:255'],
            'current_designation' => ['nullable', 'string', 'max:255'],
            'current_workplace' => ['nullable', 'string', 'max:255'],

            // Health Info
            'health_height' => ['nullable', 'string', 'max:20'],
            'health_weight' => ['nullable', 'string', 'max:20'],
            'health_eyesight' => ['nullable', 'string', 'max:50'],
            'health_blood_group' => ['nullable', 'string', 'max:10'],
            'health_blood_pressure' => ['nullable', 'string', 'max:20'],
            'health_weakness' => ['nullable', 'string'],
            'health_medical_category' => ['nullable', 'string', 'max:50'],
            'health_checkup_date' => ['nullable', 'date'],

            // Personal Info
            'ministry_name' => [$required, 'string', 'max:255'],
            'acr_period_from' => [$required, 'date'],
            'acr_period_to' => [$required, 'date'],
            'father_name' => [$required, 'string', 'max:255'],
            'mother_name' => [$required, 'string', 'max:255'],
            'date_of_birth' => [$required, 'date'],
            'prl_start_date' => ['nullable', 'date'],
            'marital_status' => [$required, 'string', 'max:50'],
            'number_of_children' => ['nullable', 'integer', 'min:0'],
            'highest_education' => [$required, 'string', 'max:255'],
            'personal_email' => ['nullable', 'email', 'max:255'],

            // Service Entry
            'govt_service_join_date' => ['nullable', 'date'],
            'gazetted_post_join_date' => ['nullable', 'date'],
            'cadre_join_date' => ['nullable', 'date'],

            // Current Position
            'position_name' => [$required, 'string', 'max:255'],
            'position_workplace' => [$required, 'string', 'max:255'],
            'position_join_date' => [$required, 'date'],
            'previous_position' => ['nullable', 'string', 'max:255'],
            'previous_workplace' => ['nullable', 'string', 'max:255'],

            // Work Description
            'work_description_1' => ['nullable', 'string'],
            'work_description_2' => ['nullable', 'string'],
            'work_description_3' => ['nullable', 'string'],
            'work_description_4' => ['nullable', 'string'],
            'work_description_5' => ['nullable', 'string'],

            'partial_acr_reason' => ['nullable', 'string'],

            // IO/CO Selection
            'initiating_officer_id' => [$required, 'exists:employees,id'],
            'countersigning_officer_id' => [$required, 'exists:employees,id'],
        ];
    }

    /**
     * Get validation rules for IO-editable fields
     */
    private function getIOValidationRules(): array
    {
        return [
            'reviewer_name' => ['required', 'string', 'max:255'],
            'reviewer_designation' => ['required', 'string', 'max:255'],
            'reviewer_workplace' => ['required', 'string', 'max:255'],
            'reviewer_id_number' => ['nullable', 'string', 'max:50'],
            'reviewer_email' => ['nullable', 'email', 'max:255'],
            'reviewer_period_from' => ['required', 'date'],
            'reviewer_period_to' => ['required', 'date'],
            'reviewer_previous_designation' => ['nullable', 'string', 'max:255'],
            'reviewer_previous_workplace' => ['nullable', 'string', 'max:255'],

            // Ratings (25 criteria)
            'rating_ethics' => ['required', 'integer', 'between:1,4'],
            'rating_honesty' => ['required', 'integer', 'between:1,4'],
            'rating_discipline' => ['required', 'integer', 'between:1,4'],
            'rating_judgment' => ['required', 'integer', 'between:1,4'],
            'rating_personality' => ['required', 'integer', 'between:1,4'],
            'rating_cooperation' => ['required', 'integer', 'between:1,4'],
            'rating_punctuality' => ['required', 'integer', 'between:1,4'],
            'rating_reliability' => ['required', 'integer', 'between:1,4'],
            'rating_responsibility' => ['required', 'integer', 'between:1,4'],
            'rating_work_interest' => ['required', 'integer', 'between:1,4'],
            'rating_following_orders' => ['required', 'integer', 'between:1,4'],
            'rating_initiative' => ['required', 'integer', 'between:1,4'],
            'rating_client_behavior' => ['required', 'integer', 'between:1,4'],
            'rating_professional_knowledge' => ['required', 'integer', 'between:1,4'],
            'rating_work_quality' => ['required', 'integer', 'between:1,4'],
            'rating_dedication' => ['required', 'integer', 'between:1,4'],
            'rating_work_quantity' => ['required', 'integer', 'between:1,4'],
            'rating_decision_making' => ['required', 'integer', 'between:1,4'],
            'rating_decision_implementation' => ['required', 'integer', 'between:1,4'],
            'rating_supervision' => ['required', 'integer', 'between:1,4'],
            'rating_teamwork_leadership' => ['required', 'integer', 'between:1,4'],
            'rating_efile_internet' => ['required', 'integer', 'between:1,4'],
            'rating_innovation' => ['required', 'integer', 'between:1,4'],
            'rating_written_expression' => ['required', 'integer', 'between:1,4'],
            'rating_verbal_expression' => ['required', 'integer', 'between:1,4'],

            // Comments
            'reviewer_additional_comments' => ['nullable', 'string'],
            'comment_type' => ['nullable', 'in:praise,adverse'],
            'reviewer_signature_date' => ['required', 'date'],
            'reviewer_memo_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get validation rules for CO-editable fields
     */
    private function getCOValidationRules(): array
    {
        return [
            'countersigner_name' => ['required', 'string', 'max:255'],
            'countersigner_designation' => ['required', 'string', 'max:255'],
            'countersigner_workplace' => ['required', 'string', 'max:255'],
            'countersigner_id_number' => ['nullable', 'string', 'max:50'],
            'countersigner_email' => ['nullable', 'email', 'max:255'],
            'countersigner_period_from' => ['required', 'date'],
            'countersigner_period_to' => ['required', 'date'],
            'countersigner_previous_designation' => ['nullable', 'string', 'max:255'],
            'countersigner_previous_workplace' => ['nullable', 'string', 'max:255'],
            'countersigner_agrees' => ['required', 'boolean'],
            'countersigner_agree_comment' => ['nullable', 'string'],
            'countersigner_disagree_comment' => ['nullable', 'string'],
            'countersigner_same_person_reason' => ['nullable', 'string'],
            'countersigner_adverse_comment' => ['nullable', 'string'],
            'countersigner_score' => ['nullable', 'integer', 'between:0,100'],
            'countersigner_signature_date' => ['required', 'date'],
            'countersigner_memo_number' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get validation rules for Dossier-editable fields
     */
    private function getDossierValidationRules(): array
    {
        return [
            'dossier_received_date' => ['required', 'date'],
            'dossier_action_taken' => ['nullable', 'string'],
            'dossier_average_score' => ['nullable', 'integer', 'between:0,100'],
        ];
    }

    /**
     * Get current user's employee profile
     */
    private function getEmployee(Request $request): ?Employee
    {
        return $request->user()->employee;
    }

    /**
     * Verify user has employee profile
     */
    private function requireEmployee(Request $request): Employee
    {
        $employee = $this->getEmployee($request);
        if (!$employee) {
            throw new Exception('Employee profile not found. Please complete your employee registration.');
        }
        return $employee;
    }

    // ==================== EMPLOYEE ENDPOINTS ====================

    /**
     * List employee's own ACRs
     */
    public function myAcrs(Request $request): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acrs = $this->workflowService->getEmployeeACRs($employee);

            return response()->json([
                'success' => true,
                'message' => 'ACR forms retrieved successfully',
                'data' => $acrs,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a new ACR (Employee)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);

            $validator = Validator::make($request->all(), $this->getEmployeeValidationRules());

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Validate IO/CO selection
            $ioErrors = $this->workflowService->validateOfficerSelection(
                $employee,
                $request->initiating_officer_id,
                $request->countersigning_officer_id
            );

            if (!empty($ioErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Officer selection validation failed',
                    'errors' => $ioErrors,
                ], 422);
            }

            $data = $validator->validated();
            $data['user_id'] = $request->user()->id;
            $data['employee_id'] = $employee->id;
            $data['status'] = ACR::STATUS_DRAFT;
            $data['current_holder'] = ACR::HOLDER_EMPLOYEE;

            $acr = ACR::create($data);

            return response()->json([
                'success' => true,
                'message' => 'ACR form created successfully',
                'data' => $acr->load(['initiatingOfficer', 'countersigningOfficer']),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show an ACR (with role-based field visibility)
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->getEmployee($request);
            $acr = ACR::with([
                'employee',
                'initiatingOfficer',
                'countersigningOfficer',
                'dossierKeeper',
                'workflowHistory',
            ])->findOrFail($id);

            // Verify access permission
            $canAccess = false;
            $role = null;
            $editableFields = [];

            if ($employee) {
                if ($acr->employee_id === $employee->id) {
                    $canAccess = true;
                    $role = 'employee';
                    $editableFields = $acr->canBeEditedByEmployee() ? ACR::EMPLOYEE_EDITABLE_FIELDS : [];
                } elseif ($acr->initiating_officer_id === $employee->id) {
                    $canAccess = true;
                    $role = 'io';
                    $editableFields = $acr->canBeEditedByIO() ? ACR::IO_EDITABLE_FIELDS : [];
                } elseif ($acr->countersigning_officer_id === $employee->id) {
                    $canAccess = true;
                    $role = 'co';
                    $editableFields = $acr->canBeEditedByCO() ? ACR::CO_EDITABLE_FIELDS : [];
                } elseif ($employee->isDossierKeeper()) {
                    $canAccess = true;
                    $role = 'dossier';
                    $editableFields = $acr->canBeEditedByDossier() ? ACR::DOSSIER_EDITABLE_FIELDS : [];
                }
            }

            // Admin users can access all ACRs
            if ($request->user()->hasRole('admin')) {
                $canAccess = true;
                $role = 'admin';
            }

            if (!$canAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this ACR.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $acr,
                'meta' => [
                    'role' => $role,
                    'editable_fields' => $editableFields,
                    'can_edit' => !empty($editableFields),
                    'status_label' => $acr->status_label,
                    'current_holder_label' => $acr->current_holder_label,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ACR not found or access denied.',
            ], 404);
        }
    }

    /**
     * Update an ACR (Employee - draft/returned only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            // Check ownership and state
            if ($acr->employee_id !== $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit this ACR.',
                ], 403);
            }

            if (!$acr->canBeEditedByEmployee()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This ACR cannot be edited in its current state.',
                ], 422);
            }

            $validator = Validator::make($request->all(), $this->getEmployeeValidationRules(true));

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Filter to only employee-editable fields
            $data = $acr->filterUpdateData($validator->validated(), $employee);
            $acr->update($data);

            return response()->json([
                'success' => true,
                'message' => 'ACR form updated successfully',
                'data' => $acr->fresh(['initiatingOfficer', 'countersigningOfficer']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete an ACR (Employee - draft only)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            if ($acr->employee_id !== $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this ACR.',
                ], 403);
            }

            if ($acr->status !== ACR::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft ACRs can be deleted.',
                ], 422);
            }

            $acr->delete();

            return response()->json([
                'success' => true,
                'message' => 'ACR form deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Submit ACR to Initiating Officer (Employee)
     */
    public function submitToIO(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            $acr = $this->workflowService->submitToIO($acr, $employee);

            return response()->json([
                'success' => true,
                'message' => 'ACR submitted to Initiating Officer successfully',
                'data' => $acr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ==================== INITIATING OFFICER ENDPOINTS ====================

    /**
     * List ACRs pending for IO
     */
    public function pendingForIO(Request $request): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);

            if (!$employee->canBeInitiatingOfficer()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to act as Initiating Officer.',
                ], 403);
            }

            $acrs = $this->workflowService->getPendingForIO($employee);

            return response()->json([
                'success' => true,
                'message' => 'Pending ACRs retrieved successfully',
                'data' => $acrs,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * IO updates ACR ratings and comments
     */
    public function ioUpdate(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            if ($acr->initiating_officer_id !== $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not the Initiating Officer for this ACR.',
                ], 403);
            }

            if (!$acr->canBeEditedByIO()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This ACR cannot be edited in its current state.',
                ], 422);
            }

            $validator = Validator::make($request->all(), array_map(function ($rules) {
                return array_map(fn($r) => $r === 'required' ? 'sometimes' : $r, $rules);
            }, $this->getIOValidationRules()));

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $acr->filterUpdateData($validator->validated(), $employee);
            $acr->update($data);

            return response()->json([
                'success' => true,
                'message' => 'ACR updated successfully',
                'data' => $acr->fresh(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * IO returns ACR to Employee
     */
    public function ioReturnToEmployee(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'return_reason' => ['required', 'string', 'min:10'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $acr = $this->workflowService->returnToEmployee($acr, $employee, $request->return_reason);

            return response()->json([
                'success' => true,
                'message' => 'ACR returned to employee successfully',
                'data' => $acr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * IO submits ACR to CO
     */
    public function ioSubmitToCO(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            $validator = Validator::make($request->all(), $this->getIOValidationRules());

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $acr = $this->workflowService->submitToCO($acr, $employee, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'ACR submitted to Countersigning Officer successfully',
                'data' => $acr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ==================== COUNTERSIGNING OFFICER ENDPOINTS ====================

    /**
     * List ACRs pending for CO
     */
    public function pendingForCO(Request $request): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);

            if (!$employee->canBeCountersigningOfficer()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to act as Countersigning Officer.',
                ], 403);
            }

            $acrs = $this->workflowService->getPendingForCO($employee);

            return response()->json([
                'success' => true,
                'message' => 'Pending ACRs retrieved successfully',
                'data' => $acrs,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * CO updates ACR
     */
    public function coUpdate(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            if ($acr->countersigning_officer_id !== $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not the Countersigning Officer for this ACR.',
                ], 403);
            }

            if (!$acr->canBeEditedByCO()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This ACR cannot be edited in its current state.',
                ], 422);
            }

            $validator = Validator::make($request->all(), array_map(function ($rules) {
                return array_map(fn($r) => $r === 'required' ? 'sometimes' : $r, $rules);
            }, $this->getCOValidationRules()));

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $acr->filterUpdateData($validator->validated(), $employee);
            $acr->update($data);

            return response()->json([
                'success' => true,
                'message' => 'ACR updated successfully',
                'data' => $acr->fresh(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * CO returns ACR to IO
     */
    public function coReturnToIO(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'return_reason' => ['required', 'string', 'min:10'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $acr = $this->workflowService->returnToIO($acr, $employee, $request->return_reason);

            return response()->json([
                'success' => true,
                'message' => 'ACR returned to Initiating Officer successfully',
                'data' => $acr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * CO submits ACR to Dossier
     */
    public function coSubmitToDossier(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            $validator = Validator::make($request->all(), $this->getCOValidationRules());

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $acr = $this->workflowService->submitToDossier($acr, $employee, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'ACR submitted to Dossier successfully',
                'data' => $acr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ==================== DOSSIER KEEPER ENDPOINTS ====================

    /**
     * List ACRs pending for Dossier
     */
    public function pendingForDossier(Request $request): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);

            if (!$employee->isDossierKeeper()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to act as Dossier Keeper.',
                ], 403);
            }

            $acrs = $this->workflowService->getPendingForDossier($employee);

            return response()->json([
                'success' => true,
                'message' => 'Pending ACRs retrieved successfully',
                'data' => $acrs,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Dossier Keeper completes ACR
     */
    public function dossierComplete(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $acr = ACR::findOrFail($id);

            $validator = Validator::make($request->all(), $this->getDossierValidationRules());

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $acr = $this->workflowService->completeDossier($acr, $employee, $validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'ACR completed successfully',
                'data' => $acr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ==================== PDF ENDPOINTS ====================

    /**
     * Get employee's ACR PDFs grouped by year
     */
    public function myPdfs(Request $request): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $pdfsByYear = $this->workflowService->getEmployeePDFsByYear($employee);

            return response()->json([
                'success' => true,
                'data' => $pdfsByYear,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Download ACR PDF (Employee only)
     */
    public function downloadPdf(Request $request, $pdfId): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);
            $pdf = AcrPdf::findOrFail($pdfId);

            // Only the employee can download their own PDFs
            if ($pdf->employee_id !== $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to download this PDF.',
                ], 403);
            }

            if (!Storage::exists($pdf->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF file not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => Storage::temporaryUrl($pdf->file_path, now()->addMinutes(5)),
                    'file_name' => $pdf->file_name,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ==================== UTILITY ENDPOINTS ====================

    /**
     * Get available Initiating Officers for selection
     */
    public function getAvailableIOs(Request $request): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);

            // Get first class officers from the same office (default)
            // and allow selection from other offices
            $ios = Employee::active()
                ->firstClass()
                ->where('id', '!=', $employee->id)
                ->with('office')
                ->orderBy('name_bangla')
                ->get(['id', 'name_bangla', 'name_english', 'designation', 'office_id']);

            return response()->json([
                'success' => true,
                'data' => $ios,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get available Countersigning Officers for selection
     */
    public function getAvailableCOs(Request $request): JsonResponse
    {
        try {
            $employee = $this->requireEmployee($request);

            $cos = Employee::active()
                ->firstClass()
                ->where('id', '!=', $employee->id)
                ->with('office')
                ->orderBy('name_bangla')
                ->get(['id', 'name_bangla', 'name_english', 'designation', 'office_id']);

            return response()->json([
                'success' => true,
                'data' => $cos,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get ACR workflow history
     */
    public function getWorkflowHistory(Request $request, $id): JsonResponse
    {
        try {
            $employee = $this->getEmployee($request);
            $acr = ACR::findOrFail($id);

            // Check access
            $canAccess = false;
            if ($employee) {
                $canAccess = $acr->employee_id === $employee->id ||
                    $acr->initiating_officer_id === $employee->id ||
                    $acr->countersigning_officer_id === $employee->id ||
                    $employee->isDossierKeeper();
            }

            if ($request->user()->hasRole('admin')) {
                $canAccess = true;
            }

            if (!$canAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this ACR history.',
                ], 403);
            }

            $history = $acr->workflowHistory()
                ->with(['performedByUser', 'performedByEmployee'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $history,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get dashboard statistics for the current user
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        try {
            $employee = $this->getEmployee($request);
            $stats = [
                'my_acrs' => [
                    'draft' => 0,
                    'pending' => 0,
                    'completed' => 0,
                    'total' => 0,
                ],
                'as_io' => [
                    'pending' => 0,
                ],
                'as_co' => [
                    'pending' => 0,
                ],
                'as_dossier' => [
                    'pending' => 0,
                ],
            ];

            if ($employee) {
                // My ACRs
                $stats['my_acrs']['draft'] = ACR::where('employee_id', $employee->id)
                    ->whereIn('status', [ACR::STATUS_DRAFT, ACR::STATUS_RETURNED_TO_EMPLOYEE])
                    ->count();

                $stats['my_acrs']['pending'] = ACR::where('employee_id', $employee->id)
                    ->whereNotIn('status', [ACR::STATUS_DRAFT, ACR::STATUS_RETURNED_TO_EMPLOYEE, ACR::STATUS_COMPLETED])
                    ->count();

                $stats['my_acrs']['completed'] = ACR::where('employee_id', $employee->id)
                    ->where('status', ACR::STATUS_COMPLETED)
                    ->count();

                $stats['my_acrs']['total'] = ACR::where('employee_id', $employee->id)->count();

                // As IO
                if ($employee->canBeInitiatingOfficer()) {
                    $stats['as_io']['pending'] = ACR::where('initiating_officer_id', $employee->id)
                        ->pendingWithIO()
                        ->count();
                }

                // As CO
                if ($employee->canBeCountersigningOfficer()) {
                    $stats['as_co']['pending'] = ACR::where('countersigning_officer_id', $employee->id)
                        ->pendingWithCO()
                        ->count();
                }

                // As Dossier
                if ($employee->isDossierKeeper()) {
                    $stats['as_dossier']['pending'] = $this->workflowService
                        ->getPendingForDossier($employee)
                        ->count();
                }
            }

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
