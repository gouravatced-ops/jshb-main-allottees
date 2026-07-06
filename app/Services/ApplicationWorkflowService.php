<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class ApplicationWorkflowService
{
    // ==========================================
    // PROCEDURES
    // ==========================================

    /**
     * Procedure 1: Create Application
     */
    public function createApplication(array $data, int $userId, int $roleId)
    {
        DB::beginTransaction();
        try {
            // Generate Application Number
            $applicationNo = $this->generateApplicationNumber($data['application_type'] ?? 'allotment');

            // Find Starting Step for the Workflow
            $startingStep = DB::table('workflow_steps')
                ->where('workflow_id', $data['workflow_id'])
                ->where('is_starting_step', 1)
                ->first();

            $applicationId = DB::table('applications')->insertGetId([
                'application_no' => $applicationNo,
                'application_type' => $data['application_type'],
                'allottee_id' => $data['allottee_id'],
                'property_id' => $data['property_id'] ?? null,
                'workflow_id' => $data['workflow_id'],
                'current_step_id' => $startingStep ? $startingStep->id : null,
                'current_user_id' => $userId,
                'current_role_id' => $roleId,
                'status' => 'pending',
                'priority' => $data['priority'] ?? 'normal',
                'created_by' => $userId,
                'created_date' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // Add Movement History (Initial setup)
            DB::table('application_movements')->insert([
                'application_id' => $applicationId,
                'from_user_id' => $userId,
                'to_user_id' => $userId,
                'from_role_id' => $roleId,
                'to_role_id' => $roleId,
                'from_step_id' => null,
                'to_step_id' => $startingStep ? $startingStep->id : null,
                'action_type' => 'created',
                'status' => 'completed',
                'remarks' => 'Application initiated',
                'movement_date' => Carbon::now(),
                'created_at' => Carbon::now()
            ]);

            // Add Audit Trail
            $this->addAuditTrail($applicationId, $userId, $roleId, 'create', 'Application created successfully.');

            DB::commit();
            return $applicationId;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Procedure 2: Forward Application
     */
    public function forwardApplication(int $applicationId, int $toUserId, int $toRoleId, int $nextStepId, string $remarks, int $fromUserId, int $fromRoleId)
    {
        DB::beginTransaction();
        try {
            $application = DB::table('applications')->where('id', $applicationId)->first();
            if (!$application) throw new Exception("Application not found.");

            // Insert Movement
            $movementId = DB::table('application_movements')->insertGetId([
                'application_id' => $applicationId,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'from_role_id' => $fromRoleId,
                'to_role_id' => $toRoleId,
                'from_step_id' => $application->current_step_id,
                'to_step_id' => $nextStepId,
                'action_type' => 'forwarded',
                'status' => 'pending',
                'remarks' => $remarks,
                'movement_date' => Carbon::now(),
                'created_at' => Carbon::now()
            ]);

            // Update Application
            DB::table('applications')->where('id', $applicationId)->update([
                'current_user_id' => $toUserId,
                'current_role_id' => $toRoleId,
                'current_step_id' => $nextStepId,
                'status' => 'forwarded',
                'updated_at' => Carbon::now()
            ]);

            // Add Note to noting sheet
            if (!empty($remarks)) {
                DB::table('application_notes')->insert([
                    'application_id' => $applicationId,
                    'movement_id' => $movementId,
                    'user_id' => $fromUserId,
                    'role_id' => $fromRoleId,
                    'note_type' => 'general',
                    'remarks' => $remarks,
                    'created_at' => Carbon::now()
                ]);
            }

            // Status history
            $this->addStatusHistory($applicationId, $application->status, 'forwarded', $fromUserId, $remarks);

            // Audit
            $this->addAuditTrail($applicationId, $fromUserId, $fromRoleId, 'forward', 'File forwarded to next step.');

            DB::commit();
            return $movementId;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Procedure 3: Approve
     */
    public function approveApplication(int $applicationId, string $remarks, int $userId, int $roleId)
    {
        DB::beginTransaction();
        try {
            $application = DB::table('applications')->where('id', $applicationId)->first();
            if (!$application) throw new Exception("Application not found.");

            DB::table('applications')->where('id', $applicationId)->update([
                'status' => 'approved',
                'updated_at' => Carbon::now()
            ]);

            $this->addStatusHistory($applicationId, $application->status, 'approved', $userId, $remarks);
            $this->addAuditTrail($applicationId, $userId, $roleId, 'approve', 'Application approved by officer.');

            if (!empty($remarks)) {
                DB::table('application_notes')->insert([
                    'application_id' => $applicationId,
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'note_type' => 'approval',
                    'remarks' => $remarks,
                    'created_at' => Carbon::now()
                ]);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Procedure 4: Reject
     */
    public function rejectApplication(int $applicationId, string $reason, int $userId, int $roleId)
    {
        DB::beginTransaction();
        try {
            $application = DB::table('applications')->where('id', $applicationId)->first();
            if (!$application) throw new Exception("Application not found.");

            DB::table('applications')->where('id', $applicationId)->update([
                'status' => 'rejected',
                'remarks' => $reason,
                'completed_date' => Carbon::now(), // Ends the workflow
                'updated_at' => Carbon::now()
            ]);

            $this->addStatusHistory($applicationId, $application->status, 'rejected', $userId, $reason);
            $this->addAuditTrail($applicationId, $userId, $roleId, 'reject', 'Application rejected.');

            if (!empty($reason)) {
                DB::table('application_notes')->insert([
                    'application_id' => $applicationId,
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'note_type' => 'rejection',
                    'remarks' => $reason,
                    'created_at' => Carbon::now()
                ]);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Procedure 5: Complete Workflow
     */
    public function completeWorkflow(int $applicationId, int $userId, int $roleId)
    {
        DB::beginTransaction();
        try {
            $application = DB::table('applications')->where('id', $applicationId)->first();
            if (!$application) throw new Exception("Application not found.");
            
            DB::table('applications')->where('id', $applicationId)->update([
                'status' => 'completed',
                'completed_date' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $this->addStatusHistory($applicationId, $application->status, 'completed', $userId, 'Workflow completed successfully.');
            $this->addAuditTrail($applicationId, $userId, $roleId, 'complete', 'Application workflow completed.');
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ==========================================
    // FUNCTIONS (Getters & Calculations)
    // ==========================================

    /**
     * Function 1: Generate Application Number
     */
    public function generateApplicationNumber(string $type = 'allotment'): string
    {
        // Generates a string like ALL-2026-85739
        $prefix = strtoupper(substr($type, 0, 3));
        $year = date('Y');
        $random = rand(10000, 99999);
        return "{$prefix}-{$year}-{$random}";
    }

    /**
     * Function 2: Get Current Officer
     */
    public function getCurrentOfficer(int $applicationId)
    {
        return DB::table('applications')
            ->join('users', 'applications.current_user_id', '=', 'users.id')
            ->join('roles', 'applications.current_role_id', '=', 'roles.id')
            ->where('applications.id', $applicationId)
            ->select('users.name as officer_name', 'roles.name as role_name', 'users.email')
            ->first();
    }

    /**
     * Function 3: Get Current Step
     */
    public function getCurrentStep(int $applicationId)
    {
        return DB::table('applications')
            ->join('workflow_steps', 'applications.current_step_id', '=', 'workflow_steps.id')
            ->where('applications.id', $applicationId)
            ->select('workflow_steps.step_name', 'workflow_steps.action_type', 'workflow_steps.is_final_step')
            ->first();
    }

    /**
     * Function 4: Get Pending Days
     */
    public function getPendingDays(int $applicationId): int
    {
        $application = DB::table('applications')->where('id', $applicationId)->first();
        if (!$application || in_array($application->status, ['completed', 'rejected', 'cancelled'])) {
            return 0;
        }

        // Calculate days from the last movement received by the current officer
        $lastMovement = DB::table('application_movements')
            ->where('application_id', $applicationId)
            ->orderBy('movement_date', 'desc')
            ->first();

        $startDate = $lastMovement ? Carbon::parse($lastMovement->movement_date) : Carbon::parse($application->created_date);
        return $startDate->diffInDays(Carbon::now());
    }

    /**
     * Function 5: Get Workflow Name
     */
    public function getWorkflowName(int $applicationId): ?string
    {
        return DB::table('applications')
            ->join('workflows', 'applications.workflow_id', '=', 'workflows.id')
            ->where('applications.id', $applicationId)
            ->value('workflows.name');
    }

    /**
     * Function 6: Get Status
     */
    public function getStatus(int $applicationId): ?string
    {
        return DB::table('applications')->where('id', $applicationId)->value('status');
    }

    // ==========================================
    // INTERNAL HELPERS
    // ==========================================

    private function addAuditTrail($applicationId, $userId, $roleId, $action, $description)
    {
        DB::table('application_audit_trails')->insert([
            'application_id' => $applicationId,
            'user_id' => $userId,
            'role_id' => $roleId,
            'action' => $action,
            'module' => 'workflow',
            'description' => $description,
            'created_at' => Carbon::now()
        ]);
    }

    private function addStatusHistory($applicationId, $fromStatus, $toStatus, $userId, $remarks)
    {
        DB::table('application_status_history')->insert([
            'application_id' => $applicationId,
            'status_from' => $fromStatus,
            'status_to' => $toStatus,
            'changed_by' => $userId,
            'remarks' => $remarks,
            'changed_at' => Carbon::now()
        ]);
    }
}
