
{{-- HEADER --}}
    <div class="page-header">
        <div>
            <h1 class="page-title"> Application </h1>
            <p class="page-subtitle">
                Key information at a glance ·
                Application :
                {{ $allottee->application_no ?? '-' }}
            </p>
        </div>
        
    </div>

@if(isset($applicationStats))
<div class="row g-3 mb-2">
    <div class="col-md-2 col-6">
        <div class="card bg-light border-0 shadow-sm text-center py-3">
            <h3 class="mb-1 fw-bold text-dark">{{ $applicationStats['total'] }}</h3>
            <span class="small text-muted text-uppercase fw-bold">Total</span>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card bg-warning bg-opacity-10 border-0 shadow-sm text-center py-3">
            <h3 class="mb-1 fw-bold text-warning-emphasis">{{ $applicationStats['pending'] }}</h3>
            <span class="small text-muted text-uppercase fw-bold">Pending</span>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card bg-info bg-opacity-10 border-0 shadow-sm text-center py-3">
            <h3 class="mb-1 fw-bold text-info-emphasis">{{ $applicationStats['in_progress'] }}</h3>
            <span class="small text-muted text-uppercase fw-bold">Processing</span>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card bg-primary bg-opacity-10 border-0 shadow-sm text-center py-3">
            <h3 class="mb-1 fw-bold text-primary-emphasis">{{ $applicationStats['approved'] }}</h3>
            <span class="small text-muted text-uppercase fw-bold">Approved</span>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card bg-success bg-opacity-10 border-0 shadow-sm text-center py-3">
            <h3 class="mb-1 fw-bold text-success-emphasis">{{ $applicationStats['completed'] }}</h3>
            <span class="small text-muted text-uppercase fw-bold">Completed</span>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card bg-danger bg-opacity-10 border-0 shadow-sm text-center py-3">
            <h3 class="mb-1 fw-bold text-danger-emphasis">{{ $applicationStats['rejected'] }}</h3>
            <span class="small text-muted text-uppercase fw-bold">Rejected</span>
        </div>
    </div>
</div>
@endif

{{-- Application List --}}
<div class="section-title d-flex justify-content-between align-items-center">
    <div>
        <i class="fa-solid fa-file-signature me-2"></i>
        My Applications
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyAgreementModal">
        <i class="fa-solid fa-plus me-1"></i> Apply for Agreement
    </button>
</div>

{{-- Apply for Agreement Modal --}}
<div class="modal fade" id="applyAgreementModal" tabindex="-1" aria-labelledby="applyAgreementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="applyAgreementModalLabel">
                    <i class="fa-solid fa-file-contract me-2"></i> Apply for Agreement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('allottee.apply.application') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="application_type" value="agreement">
                    
                    <div class="alert alert-info">
                        <strong><i class="fa-solid fa-circle-info me-1"></i> Application Process Information</strong>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark">Estimated Time:</h6>
                        <p class="text-muted small mb-0">
                            The agreement processing typically takes <strong>7 to 15 working days</strong> upon successful submission and verification by the concerned authorities.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-dark">Required Documents (May be requested):</h6>
                        <ul class="text-muted small mb-0">
                            <li>Valid Photo ID Proof (Aadhaar / PAN)</li>
                            <li>Signed Allotment Letter</li>
                            <li>Initial Payment Receipt (15% collection)</li>
                            <li>Passport Size Photographs</li>
                            <li>Affidavit (if applicable)</li>
                        </ul>
                    </div>

                    <p class="text-danger small mb-0">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Ensure all your basic details and payments are up to date before applying.
                    </p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane me-1"></i> Confirm & Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle"
                style="
                border-radius:16px;
                overflow:hidden;
            ">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-4 py-3">Application No</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">Created Date</th>
                        <th class="py-3">Current Stage</th>
                        <th class="py-3">Pending With</th>
                        <th class="py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($allApplications) && $allApplications->count() > 0)
                        @foreach($allApplications as $app)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">{{ $app->application_no }}</span>
                                @if($app->remarks)
                                    <br><small class="text-muted text-truncate d-inline-block" style="max-width: 200px;" title="{{ $app->remarks }}">{{ $app->remarks }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $app->application_type) }}</span>
                            </td>
                            <td>
                                {{ $app->created_date ? \Carbon\Carbon::parse($app->created_date)->format('d M Y, h:i A') : 'N/A' }}
                            </td>
                            <td>
                                <span class="text-dark fw-medium">{{ $app->currentStep ? $app->currentStep->step_name : 'N/A' }}</span>
                            </td>
                            <td>
                                @if($app->status == 'completed' || $app->status == 'approved' || $app->status == 'rejected')
                                    <span class="text-success"><i class="fa-solid fa-check"></i> Done</span>
                                @else
                                    <span class="badge bg-light text-dark border">{{ $app->currentRole ? $app->currentRole->name : 'System/N/A' }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($app->status == 'pending')
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Pending</span>
                                @elseif(in_array($app->status, ['in_progress', 'forwarded']))
                                    <span class="badge bg-info text-dark"><i class="fa-solid fa-spinner fa-spin me-1"></i> Processing</span>
                                @elseif($app->status == 'approved')
                                    <span class="badge bg-primary"><i class="fa-solid fa-thumbs-up me-1"></i> Approved</span>
                                @elseif($app->status == 'completed')
                                    <span class="badge bg-success"><i class="fa-solid fa-check-circle me-1"></i> Completed</span>
                                @elseif($app->status == 'rejected')
                                    <span class="badge bg-danger"><i class="fa-solid fa-times-circle me-1"></i> Rejected</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $app->status)) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-1 mb-3 opacity-25"></i>
                                <h5>No Applications Found</h5>
                                <p class="mb-0">You don't have any applications recorded in the system.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
