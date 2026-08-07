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
use App\Models\ApplicationAuditTrail;
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

        // PROCESS FLOW
        $processStage = $allottee->processSteps()->exists();
        if (!$processStage) {
            $this->processStepService->ensureProcessSteps($allottee);
            $this->processStepService->refreshStepFlow($allottee);
        } else {
            $this->processStepService->ensureProcessSteps($allottee);
        }
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
                ->post(env('DOC_UPLOAD_API_URL'), $apiPayload);

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

                // Notify the Engineer who requested the document only if ALL their requested documents are uploaded
                if ($docRequest->requested_by) {
                    $allEngineerRequests = \App\Models\DocumentRequest::where('application_id', $docRequest->application_id)
                        ->where('requested_by', $docRequest->requested_by)
                        ->get();

                    $pendingCount = $allEngineerRequests->where('status', 'pending')->count();

                    if ($pendingCount === 0) {
                        // All requested documents by this engineer have been uploaded
                        $uploadedDocNames = $allEngineerRequests->map(function ($req) {
                            return $req->documentMaster ? $req->documentMaster->document_name : 'Document';
                        })->implode(', ');

                        app(\App\Services\NotificationService::class)->send([
                            'user_id' => $docRequest->requested_by,
                            'notification_type' => 'success',
                            'subject' => 'All Requested Documents Uploaded by Allottee',
                            'message' => "The allottee ({$user->name}) has successfully uploaded all the requested documents: {$uploadedDocNames}.",
                            'send_email' => true,
                            'send_sms' => true,
                            'send_whatsapp' => true,
                            'link' => null
                        ]);
                    }
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

    public function uploadSignedAgreement(Request $request, $applicationId)
    {
        $request->validate([
            'signed_agreement_file' => 'required|file|max:5120|mimes:pdf',
        ]);

        $user = Auth::user();
        $allottee = Allottee::where('user_id', $user->id)->first();
        if (!$allottee) {
            return back()->with('error', 'Allottee profile not found.');
        }

        $application = Application::where('id', $applicationId)
            ->where('allottee_id', $allottee->id)
            ->first();

        if (!$application) {
            return back()->with('error', 'Application not found.');
        }

        // Prepare data for API
        $category = 'APPLICATION';
        $schemeCode = $allottee->scheme->scheme_code ?? 'SCH';
        $propertyNumber = $allottee->property_number ?? 'PROP';
        $yyyy = date('Y');
        $mm = date('m');
        $dd = date('d');

        $extraData = [
            'username'          => $user->username ?? '',
            'division_code'     => $allottee->division->division_code ?? '',
            'subdivision_code'  => $allottee->subDivision->subdivision_code ?? '',
            'property_category' => $allottee->propertyCategory->category_code ?? '',
            'property_type'     => $allottee->propertyType->type_code ?? '',
            'property_income'   => $allottee->quarterType->quarter_code ?? '',
            'application_for'   => $application->application_type ?? '',
        ];

        try {
            $uploadResult = $this->uploadToDocumentApi(
                $request->file('signed_agreement_file'),
                'APPLICATION',
                $schemeCode,
                $propertyNumber,
                $yyyy,
                $mm,
                $dd,
                null,
                $extraData
            );

            $receiptPath = $uploadResult['file_path'];

                // Save to ApplicationDocument
                \App\Models\ApplicationDocument::create([
                    'application_id' => $application->id,
                    'movement_id'    => null,
                    'document_type'  => 'SIGNED_AGREEMENT',
                    'document_name'  => 'Signed Agreement',
                    'file_name'      => basename($receiptPath),
                    'file_path'      => $receiptPath,
                    'file_size'      => $request->file('signed_agreement_file')->getSize(),
                    'file_mime_type' => $request->file('signed_agreement_file')->getMimeType(),
                    'uploaded_by'    => $user->id,
                    'uploader_type'  => 'Allottee',
                    'uploaded_at'    => now(),
                ]);

                // Forward the Application
                $currentStep = $application->currentStep;
                if ($currentStep) {
                    $nextStep = \App\Models\WorkflowStep::where('workflow_id', $currentStep->workflow_id)
                        ->where('step_order', '>', $currentStep->step_order)
                        ->orderBy('step_order', 'asc')
                        ->first();
                        
                    if ($nextStep) {
                        $application->current_step_id = $nextStep->id;
                        $application->current_role_id = $nextStep->role_id;
                        $application->status = 'pending';

                        // Find Target User based on division
                        $divisionId = $allottee->division_id ?? null;
                        $targetUserQuery = User::on('adms_jshb')->where('role_id', $nextStep->role_id)->where('status', 1);

                        if ($divisionId) {
                            $targetUser = (clone $targetUserQuery)->where('division_id', $divisionId)->first();
                            if (!$targetUser) {
                                $targetUser = $targetUserQuery->first();
                            }
                        } else {
                            $targetUser = $targetUserQuery->first();
                        }
                        $application->current_user_id = $targetUser ? $targetUser->id : null;
                        $application->save();

                        ApplicationMovement::create([
                            'application_id' => $application->id,
                            'from_user_id' => null, // Allottee has no adms_jshb.users record
                            'to_user_id' => $application->current_user_id,
                            'from_role_id' => $currentStep->role_id,
                            'to_role_id' => $nextStep->role_id,
                            'from_step_id' => $currentStep->id,
                            'to_step_id' => $nextStep->id,
                            'action_type' => 'forwarded',
                            'status' => 'pending',
                            'remarks' => 'Signed agreement uploaded by Allottee',
                            'movement_date' => now(),
                        ]);

                        if ($application->current_user_id) {
                            app(\App\Services\NotificationService::class)->send([
                                'user_id' => $application->current_user_id,
                                'is_allottee' => false,
                                'notification_type' => 'application_movement',
                                'subject' => 'Signed Agreement Uploaded',
                                'message' => "The allottee ({$user->name}) has uploaded their signed agreement for application {$application->application_no}. It is now forwarded to you for verification.",
                                'send_email' => true,
                                'send_sms' => false,
                                'send_whatsapp' => false,
                                'link' => null
                            ]);
                        }

                        // Notify Allottee
                        try {
                            app(\App\Services\NotificationService::class)->send([
                                'user_id' => $user->id,
                                'is_allottee' => true,
                                'notification_type' => 'application_movement',
                                'subject' => 'Signed Agreement Uploaded Successfully',
                                'message' => "Your signed agreement for application {$application->application_no} has been successfully uploaded and forwarded to the department for verification.",
                                'send_email' => true,
                                'send_sms' => false,
                                'send_whatsapp' => false,
                                'link' => null
                            ]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to send allottee notification: " . $e->getMessage());
                        }
                    }
                }

                return back()->with('success', 'Signed Agreement uploaded successfully. The application has been forwarded to the department.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Signed Agreement Upload failed: " . $e->getMessage());
            return back()->with('error', 'Error uploading document: ' . $e->getMessage());
        }
    }

    public function applyForApplication(Request $request)
    {
        $request->validate([
            'application_type' => 'required|string',
            'agreement_file' => 'nullable|file|max:5120|mimes:pdf',
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

            // Save Agreement Document for Possession Application
            if ($applicationType === 'possession') {
                $agreementFilePath = null;
                $agreementFileName = 'Signed Agreement';
                $fileSize = 0;
                $fileMimeType = 'application/pdf';

                if ($request->has('use_existing_agreement') && $request->use_existing_agreement == "1") {
                    $agreementDoc = \App\Models\AllotteeGeneratedDocument::where([
                        'allottee_id' => $allottee->id,
                        'document_type' => 'final-agreement-letter',
                    ])->latest()->first();

                    if ($agreementDoc) {
                        $agreementFilePath = $agreementDoc->signed_file_path ?? $agreementDoc->file_path;
                        $agreementFileName = $agreementDoc->signed_file_name ?? $agreementDoc->file_name;
                    }
                } elseif ($request->hasFile('agreement_file')) {
                    $schemeCode = $allottee->scheme->scheme_code ?? 'SCH';
                    $propertyNumber = $allottee->property_number ?? 'PROP';
                    $extraData = [
                        'username'          => $user->username ?? '',
                        'division_code'     => $allottee->division->division_code ?? '',
                        'subdivision_code'  => $allottee->subDivision->subdivision_code ?? '',
                        'property_category' => $allottee->propertyCategory->category_code ?? '',
                        'property_type'     => $allottee->propertyType->type_code ?? '',
                        'property_income'   => $allottee->quarterType->quarter_code ?? '',
                        'application_for'   => 'possession',
                    ];

                    $uploadResult = $this->uploadToDocumentApi(
                        $request->file('agreement_file'),
                        'APPLICATION',
                        $schemeCode,
                        $propertyNumber,
                        date('Y'),
                        date('m'),
                        date('d'),
                        null,
                        $extraData
                    );

                    $agreementFilePath = $uploadResult['file_path'];
                    $agreementFileName = basename($agreementFilePath);
                    $fileSize = $request->file('agreement_file')->getSize();
                    $fileMimeType = $request->file('agreement_file')->getMimeType();
                }

                if ($agreementFilePath) {
                    \App\Models\ApplicationDocument::create([
                        'application_id' => $application->id,
                        'movement_id'    => null,
                        'document_type'  => 'SIGNED_AGREEMENT',
                        'document_name'  => 'Signed Agreement',
                        'file_name'      => $agreementFileName,
                        'file_path'      => $agreementFilePath,
                        'file_size'      => $fileSize,
                        'file_mime_type' => $fileMimeType,
                        'uploaded_by'    => $user->id,
                        'uploader_type'  => 'Allottee',
                        'uploaded_at'    => now(),
                    ]);
                }
            }

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

            // Audit Trail for Application Creation
            ApplicationAuditTrail::create([
                'application_id' => $application->id,
                'user_id' => $user->id,
                'role_id' => $startingStep ? $startingStep->role_id : ($user->role_id ?? 17),
                'action' => 'apply',
                'module' => 'Application Workflow',
                'description' => 'Application initiated by allottee.',
                'old_data' => [],
                'new_data' => [
                    'step_id' => $startingStep ? $startingStep->id : null,
                    'role_id' => $startingStep ? $startingStep->role_id : null,
                    'action_type' => 'created',
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
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

                // Audit Trail for Auto-Forward
                ApplicationAuditTrail::create([
                    'application_id' => $application->id,
                    'user_id' => $user->id, // Allottee triggered this
                    'role_id' => $startingStep->role_id, // Use starting step's role (Allottee) instead of null to prevent DB constraint errors
                    'action' => 'forward',
                    'module' => 'Application Workflow',
                    'description' => 'Application automatically forwarded to dealing assistant.',
                    'old_data' => [
                        'step_id' => $startingStep->id,
                        'role_id' => $startingStep->role_id,
                    ],
                    'new_data' => [
                        'step_id' => $nextStep->id,
                        'role_id' => $nextStep->role_id,
                        'action_type' => 'forwarded',
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                // 1. Notify Internal Target User (Email & DB Notification)
                if ($targetUserId) {
                    try {
                        app(\App\Services\NotificationService::class)->send([
                            'user_id' => $targetUserId,
                            'is_allottee' => false,
                            'notification_type' => 'application_movement',
                            'subject' => 'New Application Assigned',
                            'message' => "A new " . str_replace('_', ' ', $applicationType) . " application ({$applicationNo}) has been submitted by {$allottee->allottee_name} and is assigned to you for processing.",
                            'send_email' => true,
                            'send_sms' => false,
                            'send_whatsapp' => false,
                            'link' => null,
                            'application_id' => $application->id,
                            'allottee_id' => $allottee->id
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send internal notification: " . $e->getMessage());
                    }
                }

                // 2. Notify Allottee (Email & DB Notification)
                try {
                    app(\App\Services\NotificationService::class)->send([
                        'user_id' => $user->id,
                        'is_allottee' => true,
                        'notification_type' => 'application_movement',
                        'subject' => 'Application Submitted Successfully',
                        'message' => "Your " . str_replace('_', ' ', $applicationType) . " application ({$applicationNo}) has been successfully submitted and forwarded to the department.",
                        'send_email' => true,
                        'send_sms' => false,
                        'send_whatsapp' => false,
                        'link' => null,
                        'application_id' => $application->id,
                        'allottee_id' => $allottee->id
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send allottee notification: " . $e->getMessage());
                }
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
