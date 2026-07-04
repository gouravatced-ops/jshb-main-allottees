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

class DashboardController extends Controller
{
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

        $allottee = \App\Models\Allottee::with([
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
        
        $blade = 'allottee-dashboard';
        $step = null;
        

        return view('module.dashboard', compact('user', 'allottee', 'latestLogin', 'steps', 'blade', 'step'));
    }

    public function section(Request $request, $blade)
    {
        $user = Auth::user();
        $allottee = \App\Models\Allottee::with([
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

        return view('module.dashboard', compact('user', 'allottee', 'latestLogin', 'steps', 'blade', 'step'));
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
