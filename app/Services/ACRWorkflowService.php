<?php

namespace App\Services;

use App\Models\ACR;
use App\Models\Employee;
use App\Models\AcrWorkflowHistory;
use App\Models\AcrPdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class ACRWorkflowService
{
    /**
     * Submit ACR from Employee to Initiating Officer
     */
    public function submitToIO(ACR $acr, Employee $employee): ACR
    {
        // Validate employee owns this ACR
        if ($acr->employee_id !== $employee->id) {
            throw new Exception('You do not have permission to submit this ACR.');
        }

        // Validate ACR is in correct state
        if (!$acr->canBeEditedByEmployee()) {
            throw new Exception('This ACR cannot be submitted in its current state.');
        }

        // Validate IO is set
        if (!$acr->initiating_officer_id) {
            throw new Exception('Please select an Initiating Officer before submitting.');
        }

        // Validate CO is set
        if (!$acr->countersigning_officer_id) {
            throw new Exception('Please select a Countersigning Officer before submitting.');
        }

        return DB::transaction(function () use ($acr, $employee) {
            $oldStatus = $acr->status;
            $oldHolder = $acr->current_holder;

            // Generate PDF snapshot of the employee's submission
            $pdfPath = $this->generatePdf($acr, $employee);

            // Take snapshot of employee data
            $acr->employee_snapshot = $employee->getSnapshotData();

            // Update ACR status
            $acr->status = ACR::STATUS_SUBMITTED_TO_IO;
            $acr->current_holder = ACR::HOLDER_IO;
            $acr->is_returned = false;
            $acr->returned_from = null;
            $acr->return_reason = null;
            $acr->sent_to_io_at = now();
            $acr->pdf_path = $pdfPath;
            $acr->pdf_generated_at = now();
            $acr->save();

            // Log workflow history
            AcrWorkflowHistory::createEntry(
                $acr,
                AcrWorkflowHistory::ACTION_SUBMITTED_TO_IO,
                $oldStatus,
                $acr->status,
                $oldHolder,
                $acr->current_holder
            );

            return $acr->fresh();
        });
    }

    /**
     * IO returns ACR to Employee for corrections
     */
    public function returnToEmployee(ACR $acr, Employee $io, string $reason): ACR
    {
        // Validate IO
        if ($acr->initiating_officer_id !== $io->id) {
            throw new Exception('You are not the Initiating Officer for this ACR.');
        }

        // Validate ACR can be returned
        if (!$acr->canIOReturnToEmployee()) {
            throw new Exception('This ACR cannot be returned in its current state.');
        }

        if (empty($reason)) {
            throw new Exception('Please provide a reason for returning the ACR.');
        }

        return DB::transaction(function () use ($acr, $io, $reason) {
            $oldStatus = $acr->status;
            $oldHolder = $acr->current_holder;

            $acr->status = ACR::STATUS_RETURNED_TO_EMPLOYEE;
            $acr->current_holder = ACR::HOLDER_EMPLOYEE;
            $acr->is_returned = true;
            $acr->returned_from = 'io';
            $acr->return_reason = $reason;
            $acr->returned_at = now();
            $acr->save();

            AcrWorkflowHistory::createEntry(
                $acr,
                AcrWorkflowHistory::ACTION_RETURNED_TO_EMPLOYEE,
                $oldStatus,
                $acr->status,
                $oldHolder,
                $acr->current_holder,
                null,
                $reason
            );

            return $acr->fresh();
        });
    }

    /**
     * IO submits ACR to Countersigning Officer
     */
    public function submitToCO(ACR $acr, Employee $io, array $ioData = []): ACR
    {
        // Validate IO
        if ($acr->initiating_officer_id !== $io->id) {
            throw new Exception('You are not the Initiating Officer for this ACR.');
        }

        // Validate ACR is in correct state
        if (!$acr->canBeEditedByIO()) {
            throw new Exception('This ACR cannot be forwarded in its current state.');
        }

        return DB::transaction(function () use ($acr, $io, $ioData) {
            $oldStatus = $acr->status;
            $oldHolder = $acr->current_holder;

            // Filter and update only IO-allowed fields
            $filteredData = $acr->filterUpdateData($ioData, $io);
            $acr->fill($filteredData);

            // Take snapshot of IO data
            $acr->io_snapshot = $io->getSnapshotData();

            $acr->status = ACR::STATUS_SUBMITTED_TO_CO;
            $acr->current_holder = ACR::HOLDER_CO;
            $acr->io_completed_at = now();
            $acr->sent_to_co_at = now();
            $acr->reviewed_date = now();
            $acr->save();

            AcrWorkflowHistory::createEntry(
                $acr,
                AcrWorkflowHistory::ACTION_SUBMITTED_TO_CO,
                $oldStatus,
                $acr->status,
                $oldHolder,
                $acr->current_holder
            );

            return $acr->fresh();
        });
    }

    /**
     * CO returns ACR to IO for corrections
     */
    public function returnToIO(ACR $acr, Employee $co, string $reason): ACR
    {
        // Validate CO
        if ($acr->countersigning_officer_id !== $co->id) {
            throw new Exception('You are not the Countersigning Officer for this ACR.');
        }

        // Validate ACR can be returned
        if (!$acr->canCOReturnToIO()) {
            throw new Exception('This ACR cannot be returned in its current state.');
        }

        if (empty($reason)) {
            throw new Exception('Please provide a reason for returning the ACR.');
        }

        return DB::transaction(function () use ($acr, $co, $reason) {
            $oldStatus = $acr->status;
            $oldHolder = $acr->current_holder;

            $acr->status = ACR::STATUS_RETURNED_TO_IO;
            $acr->current_holder = ACR::HOLDER_IO;
            $acr->is_returned = true;
            $acr->returned_from = 'co';
            $acr->return_reason = $reason;
            $acr->returned_at = now();
            $acr->save();

            AcrWorkflowHistory::createEntry(
                $acr,
                AcrWorkflowHistory::ACTION_RETURNED_TO_IO,
                $oldStatus,
                $acr->status,
                $oldHolder,
                $acr->current_holder,
                null,
                $reason
            );

            return $acr->fresh();
        });
    }

    /**
     * CO submits ACR to Dossier
     */
    public function submitToDossier(ACR $acr, Employee $co, array $coData = []): ACR
    {
        // Validate CO
        if ($acr->countersigning_officer_id !== $co->id) {
            throw new Exception('You are not the Countersigning Officer for this ACR.');
        }

        // Validate ACR is in correct state
        if (!$acr->canBeEditedByCO()) {
            throw new Exception('This ACR cannot be forwarded in its current state.');
        }

        return DB::transaction(function () use ($acr, $co, $coData) {
            $oldStatus = $acr->status;
            $oldHolder = $acr->current_holder;

            // Filter and update only CO-allowed fields
            $filteredData = $acr->filterUpdateData($coData, $co);
            $acr->fill($filteredData);

            // Take snapshot of CO data
            $acr->co_snapshot = $co->getSnapshotData();

            $acr->status = ACR::STATUS_SUBMITTED_TO_DOSSIER;
            $acr->current_holder = ACR::HOLDER_DOSSIER;
            $acr->co_completed_at = now();
            $acr->sent_to_dossier_at = now();
            $acr->countersigned_date = now();
            $acr->save();

            AcrWorkflowHistory::createEntry(
                $acr,
                AcrWorkflowHistory::ACTION_SUBMITTED_TO_DOSSIER,
                $oldStatus,
                $acr->status,
                $oldHolder,
                $acr->current_holder
            );

            return $acr->fresh();
        });
    }

    /**
     * Dossier Keeper completes the ACR
     */
    public function completeDossier(ACR $acr, Employee $dossierKeeper, array $dossierData = []): ACR
    {
        // Validate Dossier Keeper role
        if (!$dossierKeeper->isDossierKeeper()) {
            throw new Exception('You do not have Dossier Keeper permissions.');
        }

        // Validate ACR is in correct state
        if (!$acr->canBeEditedByDossier()) {
            throw new Exception('This ACR cannot be completed in its current state.');
        }

        return DB::transaction(function () use ($acr, $dossierKeeper, $dossierData) {
            $oldStatus = $acr->status;
            $oldHolder = $acr->current_holder;

            // Filter and update only Dossier-allowed fields
            $filteredData = array_intersect_key($dossierData, array_flip(ACR::DOSSIER_EDITABLE_FIELDS));
            $acr->fill($filteredData);

            $acr->dossier_keeper_id = $dossierKeeper->id;
            $acr->status = ACR::STATUS_COMPLETED;
            $acr->current_holder = ACR::HOLDER_COMPLETED;
            $acr->completed_at = now();
            $acr->save();

            AcrWorkflowHistory::createEntry(
                $acr,
                AcrWorkflowHistory::ACTION_DOSSIER_COMPLETED,
                $oldStatus,
                $acr->status,
                $oldHolder,
                $acr->current_holder
            );

            return $acr->fresh();
        });
    }

    /**
     * Generate PDF for ACR
     */
    public function generatePdf(ACR $acr, Employee $employee): string
    {
        // Load relationships
        $acr->load(['employee', 'initiatingOfficer', 'countersigningOfficer']);

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('pdf.acr', [
            'acr' => $acr,
            'employee' => $employee,
        ]);

        // Create directory structure: acr_pdfs/{employee_id}/{year}/
        $directory = "acr_pdfs/{$employee->id}/{$acr->reporting_year}";

        // Count existing PDFs for this employee-year combination to determine sequence
        $existingCount = AcrPdf::where('employee_id', $employee->id)
            ->where('reporting_year', $acr->reporting_year)
            ->count();

        $sequence = $existingCount + 1;
        $isPartial = $acr->isPartial();

        // Generate filename
        $filename = sprintf(
            'ACR_%s_%s_%s_%d.pdf',
            $employee->employee_id,
            $acr->reporting_year,
            now()->format('Ymd_His'),
            $sequence
        );

        $filePath = "{$directory}/{$filename}";

        // Store the PDF
        Storage::put($filePath, $pdf->output());

        // Calculate checksum
        $checksum = hash('sha256', $pdf->output());

        // Create PDF record
        AcrPdf::create([
            'acr_id' => $acr->id,
            'employee_id' => $employee->id,
            'reporting_year' => $acr->reporting_year,
            'file_name' => $filename,
            'file_path' => $filePath,
            'file_size' => Storage::size($filePath),
            'checksum' => $checksum,
            'is_partial' => $isPartial,
            'partial_sequence' => $sequence,
            'generated_at' => now(),
        ]);

        // Log PDF generation
        AcrWorkflowHistory::createEntry(
            $acr,
            AcrWorkflowHistory::ACTION_PDF_GENERATED,
            $acr->status,
            $acr->status
        );

        return $filePath;
    }

    /**
     * Get ACRs pending for a specific IO
     */
    public function getPendingForIO(Employee $io)
    {
        return ACR::where('initiating_officer_id', $io->id)
            ->pendingWithIO()
            ->with(['employee', 'employee.office'])
            ->orderBy('sent_to_io_at', 'asc')
            ->get();
    }

    /**
     * Get ACRs pending for a specific CO
     */
    public function getPendingForCO(Employee $co)
    {
        return ACR::where('countersigning_officer_id', $co->id)
            ->pendingWithCO()
            ->with(['employee', 'employee.office', 'initiatingOfficer'])
            ->orderBy('sent_to_co_at', 'asc')
            ->get();
    }

    /**
     * Get ACRs pending for Dossier
     */
    public function getPendingForDossier(Employee $dossierKeeper)
    {
        // Get the office of the dossier keeper
        $officeId = $dossierKeeper->office_id;

        return ACR::pendingWithDossier()
            ->whereHas('employee', function ($query) use ($officeId) {
                $query->where('office_id', $officeId);
            })
            ->with(['employee', 'employee.office', 'initiatingOfficer', 'countersigningOfficer'])
            ->orderBy('sent_to_dossier_at', 'asc')
            ->get();
    }

    /**
     * Get employee's own ACRs
     */
    public function getEmployeeACRs(Employee $employee)
    {
        return ACR::where('employee_id', $employee->id)
            ->with(['initiatingOfficer', 'countersigningOfficer', 'latestPdf'])
            ->orderBy('reporting_year', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get employee's ACR PDFs grouped by year
     */
    public function getEmployeePDFsByYear(Employee $employee)
    {
        return AcrPdf::where('employee_id', $employee->id)
            ->orderBy('reporting_year', 'desc')
            ->orderBy('partial_sequence', 'asc')
            ->get()
            ->groupBy('reporting_year');
    }

    /**
     * Validate IO/CO selection rules
     */
    public function validateOfficerSelection(Employee $employee, ?int $ioId, ?int $coId): array
    {
        $errors = [];

        if ($ioId) {
            $io = Employee::find($ioId);
            if (!$io) {
                $errors['initiating_officer_id'] = 'Initiating Officer not found.';
            } elseif (!$io->canBeInitiatingOfficer()) {
                $errors['initiating_officer_id'] = 'Selected person cannot be an Initiating Officer (must be 1st class officer).';
            } elseif ($io->id === $employee->id) {
                $errors['initiating_officer_id'] = 'You cannot select yourself as Initiating Officer.';
            }
        }

        if ($coId) {
            $co = Employee::find($coId);
            if (!$co) {
                $errors['countersigning_officer_id'] = 'Countersigning Officer not found.';
            } elseif (!$co->canBeCountersigningOfficer()) {
                $errors['countersigning_officer_id'] = 'Selected person cannot be a Countersigning Officer (must be 1st class officer).';
            } elseif ($co->id === $employee->id) {
                $errors['countersigning_officer_id'] = 'You cannot select yourself as Countersigning Officer.';
            }
        }

        // IO and CO can be the same person (valid case)

        return $errors;
    }
}
