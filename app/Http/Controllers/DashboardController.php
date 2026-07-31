<?php

namespace App\Http\Controllers;

use App\Models\EngineerDetail;
use App\Models\GuestHouseRequisition;
use App\Models\LoginLog;
use App\Models\Organization;
use App\Models\OtpLog;
use App\Models\PostType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ProcessStepService;
use App\Models\Application;
use App\Models\Allottee;
use App\Models\AllotteeDocument;
use App\Models\DocumentRequest;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\ApplicationMovement;
use App\Traits\DocumentUploadTrait;

class DashboardController extends Controller
{
    use DocumentUploadTrait;

    protected $processStepService;

    public function __construct(ProcessStepService $processStepService)
    {
        $this->processStepService = $processStepService;
    }
    public function index(Request $request)
    {
        if ($this->checkSessionExpiry($request)) {
            return redirect()->route('login')->with('error', 'Your session expired after 60 minutes.');
        }

        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        if ($redirect = $this->redirectIfLocked()) {
            return $redirect;
        }

        $user = Auth::user();

        $allottee = Allottee::with([
            'alloteeAdresses',
            'nomineesBank',
            'documentData',
            'generatedDocument',
            'emiAccount',
            'emiDemand',
            'emiSchedule',
            'accountLedger',
            'allotteeOrders',
            'allotteeTransaction',
            'processSteps',
            'siteVerification',
            'allotProFinDetail',
            'scheme',
            'propertyCategory',
            'propertyType',
            'quarterType'
        ])->where('user_id', $user->id)->first();

        $latestLogin = $user->loginLogs()->latest()->first();

        // Ensure process steps exist for this allottee
        $this->processStepService->ensureProcessSteps($allottee);

        $steps = $allottee->processSteps()->orderBy('step_no')->get();

        $pendingApplication = Application::where('allottee_id', $allottee->id)
            ->whereIn('status', ['pending', 'in_progress', 'forwarded'])
            ->first();


        $blade = 'allottee-dashboard';
        $step = null;

        $documentRequests = \App\Models\DocumentRequest::where('allottee_id', $allottee->id)
            ->where('status', 'pending')
            ->with(['documentMaster', 'requestedBy'])
            ->get();

        return view('module.dashboard', compact('user', 'allottee', 'latestLogin', 'steps', 'blade', 'step', 'pendingApplication', 'documentRequests'));
    }

    public function section(Request $request, $blade)
    {
        $user = Auth::user();
        $allottee = Allottee::with([
            'alloteeAdresses',
            'nomineesBank',
            'documentData',
            'generatedDocument',
            'emiAccount',
            'emiDemand',
            'emiSchedule',
            'accountLedger',
            'allotteeOrders',
            'allotteeTransaction',
            'processSteps',
            'siteVerification',
            'allotProFinDetail',
            'scheme',
            'propertyCategory',
            'propertyType',
            'quarterType'
        ])->where('user_id', $user->id)->firstOrFail();

        $latestLogin = $user->loginLogs()->latest()->first();

        $this->processStepService->ensureProcessSteps($allottee);
        $steps = $allottee->processSteps()->orderBy('step_no')->get();

        // Find the step corresponding to this blade
        $step = $steps->firstWhere('blade', $blade);

        $pendingApplication = Application::where('allottee_id', $allottee->id)
            ->whereIn('status', ['pending', 'in_progress', 'forwarded'])
            ->first();

        $applicationStats = null;
        $allApplications = null;
        if ($blade === 'application') {
            $allApplications = Application::with(['currentStep', 'currentRole'])->where('allottee_id', $allottee->id)->orderBy('created_at', 'desc')->get();
            $applicationStats = [
                'total' => $allApplications->count(),
                'pending' => $allApplications->where('status', 'pending')->count(),
                'in_progress' => $allApplications->whereIn('status', ['in_progress', 'forwarded'])->count(),
                'approved' => $allApplications->where('status', 'approved')->count(),
                'completed' => $allApplications->where('status', 'completed')->count(),
                'rejected' => $allApplications->where('status', 'rejected')->count(),
            ];
        }

        $notifications = null;
        if ($blade === 'notifications') {
            $notifications = \App\Models\Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('module.dashboard', compact('user', 'allottee', 'latestLogin', 'steps', 'blade', 'step', 'pendingApplication', 'applicationStats', 'allApplications', 'notifications'));
    }

    public function uploadDocumentRequest(Request $request)
    {
        $request->validate([
            'document_request_id' => 'required',
            'document_master_id' => 'required',
            'document_file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $user = Auth::user();
        $allottee = Allottee::where('user_id', $user->id)->first();
        if (!$allottee) {
            return back()->with('error', 'Allottee profile not found.');
        }

        $docRequest = DocumentRequest::find($request->document_request_id);
        if (!$docRequest || $docRequest->allottee_id != $allottee->id) {
            return back()->with('error', 'Invalid document request.');
        }

        if ($docRequest->expires_at && $docRequest->expires_at->isPast()) {
            return back()->with('error', 'This document request has expired.');
        }

        $documentMaster = \App\Models\DocumentMaster::find($request->document_master_id);
        $docName = $documentMaster ? $documentMaster->document_name : 'Document';

        // Prepare data for API
        $category = 'FINAL';
        $schemeCode = $allottee->scheme->scheme_code ?? 'SCH';
        $propertyNumber = $allottee->property_number ?? 'PROP';
        $yyyy = date('Y');
        $mm = date('m');
        $dd = date('d');

        // Note: application is not defined in this scope, but kept for payload structure
        $application_for = '';

        $apiPayload = [
            'project'           => 'jshb-allottee',
            'category'          => $category,
            'username'          => $user->username ?? '',
            'property_number'   => $propertyNumber,
            'document_name'     => $docName,
            'division_code'     => $allottee->division->division_code ?? '',
            'subdivision_code'  => $allottee->subDivision->subdivision_code ?? '',
            'property_category' => $allottee->propertyCategory->category_code ?? '',
            'yyyy'              => $yyyy,
            'mm'                => $mm,
            'dd'                => $dd,
            'property_type'     => $allottee->propertyType->type_code ?? '',
            'property_income'   => $allottee->quarterType->quarter_code ?? '',
            'application_for'   => $application_for,
            'scheme_code'       => $schemeCode,
        ];

        try {
            \Illuminate\Support\Facades\Log::info("Document Request Upload API Payload: ", $apiPayload);

            $response = \Illuminate\Support\Facades\Http::withToken(env('DOC_API_TOKEN'))
                ->withHeaders(['X-API-KEY' => env('DOC_API_TOKEN')])
                ->attach('file', file_get_contents($request->file('document_file')), $request->file('document_file')->getClientOriginalName())
                ->post(env('DOC_API_URL'), $apiPayload);

            \Illuminate\Support\Facades\Log::info("Document Request Upload API Response Status: " . $response->status());
            \Illuminate\Support\Facades\Log::info("Document Request Upload API Response Body: " . $response->body());

            if ($response->successful() && $response->json('status') === 'success') {
                $responseData = $response->json('data');
                $receiptPath = ltrim($responseData['file_path'], '/');
                $receiptFile = basename($receiptPath);

                $allotteeDoc = AllotteeDocument::create([
                    'allottee_id' => $allottee->id,
                    'document_id' => $request->document_master_id,
                    'document_type' => $docName,
                    'file_path' => $receiptPath,
                    'file_name' => $receiptFile,
                    'remarks' => 'Uploaded as per engineer request.',
                    'uploaded_by' => $user->id
                ]);

                $docRequest->update([
                    'status' => 'uploaded',
                    'uploaded_document_id' => $allotteeDoc->id
                ]);

                // Notify the Engineer who requested the document
                if ($docRequest->requested_by) {
                    app(\App\Services\NotificationService::class)->send([
                        'user_id' => $docRequest->requested_by,
                        'notification_type' => 'success',
                        'subject' => 'Document Uploaded by Allottee',
                        'message' => "The allottee ({$user->name}) has uploaded the requested document: {$docName}.",
                        'send_email' => true,
                        'send_sms' => true, // Since it's to an engineer, maybe we enable sms/whatsapp as per config
                        'send_whatsapp' => true,
                        'link' => null
                    ]);
                }

                return back()->with('success', 'Document uploaded successfully.');
            } else {
                return back()->with('error', 'Failed to upload document to API: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Document Request Upload failed: " . $e->getMessage());
            return back()->with('error', 'Error uploading document: ' . $e->getMessage());
        }
    }

    public function applyForApplication(Request $request)
    {
        $request->validate([
            'application_type' => 'required|string',
        ]);

        $user = Auth::user();
        $allottee = Allottee::where('user_id', $user->id)->first();
        if (!$allottee) {
            return back()->with('error', 'Allottee profile not found.');
        }

        $applicationType = $request->application_type;

        // Find Workflow
        $workflow = Workflow::where('application_type', $applicationType)
            ->where('is_active', 1)
            ->first();

        if (!$workflow) {
            return back()->with('error', 'Workflow not found for this application type.');
        }

        $existingApplication = Application::where('allottee_id', $allottee->id)
            ->where('application_type', $applicationType)
            ->exists();

        if ($existingApplication) {
            return back()->with('error', 'You have already applied for this application.');
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $startingStep = WorkflowStep::where('workflow_id', $workflow->id)
                ->orderBy('step_order', 'asc')
                ->first();

            $nextStep = $startingStep ? WorkflowStep::where('workflow_id', $workflow->id)
                ->where('step_order', '>', $startingStep->step_order)
                ->orderBy('step_order', 'asc')
                ->first() : null;

            // Find Target User based on division from adms_jshb database
            $divisionId = $allottee->division_id;
            $targetUserId = $nextStep ? \Illuminate\Support\Facades\DB::connection('adms_jshb')->table('users')
                ->where('role_id', $nextStep->role_id)
                ->when($divisionId, function ($query) use ($divisionId) {
                    return $query->where('division_id', $divisionId);
                })
                ->where('status', 1)
                ->orderByDesc('is_default')
                ->value('id') : null;

            $applicationNo = 'APL-' . date('Y') . '-' . rand(12345678, 99999999);

            $application = Application::create([
                'application_no' => $applicationNo,
                'application_type' => $applicationType,
                'allottee_id' => $allottee->id,
                'property_id' => $allottee->property_number ? 1 : 1, // dummy for now
                'workflow_id' => $workflow->id,
                'current_step_id' => $nextStep ? $nextStep->id : ($startingStep ? $startingStep->id : null),
                'current_user_id' => $targetUserId,
                'current_role_id' => $nextStep ? $nextStep->role_id : ($startingStep ? $startingStep->role_id : null),
                'status' => 'in_progress',
                'priority' => 'normal',
                'created_date' => now(),
                'remarks' => 'New ' . $applicationType . ' application initiated by allottee',
                'created_by' => $user->id,
            ]);

            // Add Movement Log - First Row (Created)
            ApplicationMovement::create([
                'application_id' => $application->id,
                'from_user_id' => null, // null because allottee is not a jshb internal user
                'to_user_id' => null,
                'from_role_id' => $user->role_id,
                'to_role_id' => $startingStep ? $startingStep->role_id : null,
                'from_step_id' => null,
                'to_step_id' => $startingStep ? $startingStep->id : null,
                'action_type' => 'created',
                'status' => 'completed',
                'remarks' => 'Application initiated by allottee.',
                'movement_date' => now(),
            ]);

            // Add Movement Log - Second Row (Forwarded)
            if ($startingStep && $nextStep) {
                ApplicationMovement::create([
                    'application_id' => $application->id,
                    'from_user_id' => null, // null because allottee is not a jshb internal user
                    'to_user_id' => $targetUserId,
                    'from_role_id' => $startingStep->role_id,
                    'to_role_id' => $nextStep->role_id,
                    'from_step_id' => $startingStep->id,
                    'to_step_id' => $nextStep->id,
                    'action_type' => 'forwarded',
                    'status' => 'pending',
                    'remarks' => 'Application automatically forwarded to dealing assistant.',
                    'movement_date' => now(),
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            return back()->with('success', 'Application submitted successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Application Creation failed: " . $e->getMessage());
            return back()->with('error', 'Error creating application: ' . $e->getMessage());
        }
    }

    private function checkSessionExpiry(Request $request)
    {
        if (! Auth::check()) {
            return false;
        }

        $expiryTs = $request->session()->get('session_expires_at_ts');

        if ($expiryTs && now()->timestamp >= $expiryTs) {
            $user = Auth::user();

            LoginLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
                'action' => 'auto_logout',
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return true;
        }

        return false;
    }
}
