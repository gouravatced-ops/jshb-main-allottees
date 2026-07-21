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
