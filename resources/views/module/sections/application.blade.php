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
    <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyAgreementModal">
        <i class="fa-solid fa-plus me-1"></i> Apply for Agreement
    </button> -->
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
                        <th class="py-3 text-center">Action</th>
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
                        <td class="text-center">
                            @if($app->currentStep && $app->currentStep->step_code === 'agreement-allottee-upload')
                            @php
                            $agreementDocument = \App\Models\ApplicationDocument::where([
                            'application_id' => $app->id,
                            'document_type' => 'AGREEMENT_LETTER'
                            ])->latest()->first();

                            $signedAgreementDocument = \App\Models\ApplicationDocument::where([
                            'application_id' => $app->id,
                            'document_type' => 'SIGNED_AGREEMENT'
                            ])->latest()->first();
                            @endphp
                            @if($agreementDocument)
                            <a href="{{ route('media.document', ['path' => rtrim(env('DOC_API_URL', 'http://localhost/jshb-doc'), '/') . '/' . ltrim($agreementDocument->file_path, '/')]) }}" target="_blank" class="btn btn-sm btn-outline-primary mb-1 d-block" title="Download Agreement">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                            @if(!$signedAgreementDocument)
                            <button type="button" class="btn btn-sm btn-success w-100" data-bs-toggle="modal" data-bs-target="#reuploadConfirmModal_{{ $app->id }}">
                                <i class="fa-solid fa-upload"></i> Upload Signed
                            </button>

                            <!-- Upload Signed Agreement Modal -->
                            <div class="modal fade text-start" id="reuploadConfirmModal_{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                                        <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #eaeaea; border-radius: 12px 12px 0 0;">
                                            <h5 class="modal-title" style="font-weight: 600; color: #333;"><i class="fa-solid fa-file-signature text-primary me-2"></i> Upload Signed Agreement</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('allottee.applications.upload-signed-agreement', $app->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-body" style="padding: 20px;">
                                                <div class="alert alert-warning" style="background: #fff3cd; border-color: #ffecb5; color: #664d03; border-radius: 8px;">
                                                    <strong><i class="fa-solid fa-circle-exclamation"></i> Instructions:</strong> Please download the generated agreement, sign it physically or digitally, and upload the scanned copy below <b>within 5 days</b>.
                                                </div>
                                                <div class="mb-3 mt-4">
                                                    <label class="form-label" style="font-weight: 600; color: #495057;">Select Signed File (PDF only) <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="file" name="signed_agreement_file" accept=".pdf" required style="padding: 10px; border-radius: 6px;">
                                                    <div class="form-text mt-2"><i class="fa-solid fa-circle-info text-info"></i> Max file size: 5MB.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="border-top: 1px solid #eaeaea;">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-weight: 500; border-radius: 6px;">Cancel</button>
                                                <button type="submit" class="btn btn-success" style="font-weight: 500; border-radius: 6px; background: #28a745;"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @else
                            <span class="badge bg-success w-100"><i class="fa-solid fa-check"></i> Uploaded</span>
                            @endif
                            @endif
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
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
