<div id="page-dashboard" class="admin-dashboard-page">

    @if(!$allottee)
        <div class="alert alert-warning">
            <h4>No Allottee Record Found</h4>
            <p>Your user account is not currently linked to an active allottee profile.</p>
        </div>
    @else
        <!-- Hero Section -->
        <div class="dashboard-hero-card" style="background: linear-gradient(135deg, #0d6e55, #0a3d31); border-radius: 8px; padding: 20px; color: white; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4">
                <div>
                    <div style="color: #facc15; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
                        Application #{{ $allottee->application_no ?? 'N/A' }}
                    </div>
                    <h2 style="font-weight: 700; margin-bottom: 10px; font-size: 24px;">
                        {{ trim(($allottee->prefix ?? '') . ' ' . ($allottee->allottee_name ?? '') . ' ' . ($allottee->allottee_surname ?? '')) }}
                    </h2>
                    <p style="margin: 0; opacity: 0.9; font-size: 15px; max-width: 600px;">
                        <i class="fa-solid fa-map-pin me-1"></i> {{ $allottee->propertyCategory->name ?? 'Category' }} - {{ $allottee->propertyType->name ?? 'Property' }} ({{ $allottee->quarterType->quarter_name ?? 'Type' }}) <br>
                        <i class="fa-solid fa-layer-group me-1 mt-2"></i> {{ $allottee->scheme->scheme_name ?? 'Scheme Not Assigned' }}
                    </p>
                </div>
                
                <div class="text-md-end text-start p-3" style="background: rgba(255,255,255,0.1); border-radius: 10px; backdrop-filter: blur(10px);">
                    <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Payment Mode</div>
                    <div style="font-size: 20px; font-weight: 700; color: #fff;">
                        @if($allottee->payment_option == 'emi')
                            <i class="fa-solid fa-calendar-days me-2"></i> EMI / Installments
                        @elseif($allottee->payment_option == 'one_time')
                            <i class="fa-solid fa-money-check-dollar me-2"></i> One-Time Payment
                        @else
                            <i class="fa-solid fa-circle-question me-2"></i> Pending Choice
                        @endif
                    </div>
                    <div class="badge bg-success mt-2" style="font-size: 12px; padding: 5px 10px;">Active Allottee</div>
                </div>
            </div>
        </div>

        @php
            $totalPaid = $allottee->allotteeTransaction()->where('payment_status', 'success')->sum('amount') ?? 0;
            $outstandingDemand = $allottee->emiDemand()->whereIn('demand_status', ['pending', 'partial', 'overdue'])->sum('outstanding_amount') ?? 0;
            $totalDocuments = $allottee->documentData()->count() + $allottee->generatedDocument()->count();
            
            $nextEmi = $allottee->emiDemand()->whereIn('demand_status', ['pending', 'partial', 'overdue'])->orderBy('due_date', 'asc')->first();
            $completedStepsCount = $allottee->processSteps()->where('status', 'completed')->count();
            $totalStepsCount = $allottee->processSteps()->count();
            $progressPercent = $totalStepsCount > 0 ? round(($completedStepsCount / $totalStepsCount) * 100) : 0;
            
            $contact = $allottee->alloteeAdresses; // relation from model
            $mobile = $contact->mobile_number ?? 'Not Provided';
            $email = $contact->email ?? 'Not Provided';
            $district = $contact->present_district ?? $contact->permanent_district ?? '-';
            $post_office = $contact->present_post_office ?? $contact->permanent_post_office ?? '-';
        @endphp

        <!-- Application Status Card -->
        @if(isset($pendingApplication) && $pendingApplication)
        <div class="row mb-1">
            <div class="col-12">
                <div class="card border-0" style="background: linear-gradient(to right, #fff3cd, #fff8e1); border-left: 4px solid #ffc107 !important; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 8px;">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; background: rgba(255, 193, 7, 0.2); color: #b8860b;">
                                <i class="fa-solid fa-file-signature fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">Application Status: <span class="text-warning-emphasis">Pending</span></h6>
                                <p class="mb-0 text-muted small">
                                    You have a pending <strong>{{ ucfirst(str_replace('_', ' ', $pendingApplication->application_type)) }}</strong> application. 
                                    (Application No: {{ $pendingApplication->application_no ?? '-' }})
                                </p>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark px-3 py-2" style="font-size: 13px; font-weight: 600;">Processing</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Stats Row -->
        <div class="row g-4 mb-4">
            <!-- Total Paid -->
            <div class="col-xl-3 col-lg-6">
                <div class="card h-100 border-0" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px;">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background: rgba(13, 110, 85, 0.1); color: #0d6e55;">
                            <i class="fa-solid fa-wallet fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase mb-1">Total Amount Paid</div>
                            <h3 class="mb-0 fw-bold" style="color: #0f1f1a;">₹ {{ number_format($totalPaid, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Outstanding -->
            <div class="col-xl-3 col-lg-6">
                <div class="card h-100 border-0" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px;">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background: rgba(220, 38, 38, 0.1); color: #dc2626;">
                            <i class="fa-solid fa-file-invoice-dollar fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase mb-1">Current Outstanding</div>
                            <h3 class="mb-0 fw-bold" style="color: #dc2626;">₹ {{ number_format($outstandingDemand, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="col-xl-3 col-lg-6">
                <div class="card h-100 border-0" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px;">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                            <i class="fa-solid fa-file-shield fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase mb-1">Documents</div>
                            <h3 class="mb-0 fw-bold" style="color: #0f1f1a;">{{ $totalDocuments }} <span class="fs-6 text-muted fw-normal">Files</span></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Process Status -->
            <div class="col-xl-3 col-lg-6">
                <div class="card h-100 border-0" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="text-muted small fw-bold text-uppercase">Process Completion</div>
                            <div class="badge bg-primary rounded-pill">{{ $progressPercent }}%</div>
                        </div>
                        <div class="progress mt-3" style="height: 10px; border-radius: 10px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $progressPercent }}%"></div>
                        </div>
                        <div class="mt-2 text-muted small">{{ $completedStepsCount }} of {{ $totalStepsCount }} steps completed</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Details Column -->
            <div class="col-lg-8">
                <!-- Notices -->
                <div class="card border-0 mb-4" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 px-4 bg-light border-bottom">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-bullhorn text-warning me-2"></i>Notices & Announcements</h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex w-100 justify-content-between mb-1">
                                    <h6 class="mb-0 fw-bold text-dark">Extension of EMI Payment Deadline</h6>
                                    <small class="text-muted">3 days ago</small>
                                </div>
                                <p class="mb-0 text-muted small">The board has decided to extend the deadline for the current month's EMI payment without any late fees until the 15th of the month.</p>
                            </div>
                            <div class="list-group-item p-3">
                                <div class="d-flex w-100 justify-content-between mb-1">
                                    <h6 class="mb-0 fw-bold text-dark">Property Registration Camp</h6>
                                    <small class="text-muted">1 week ago</small>
                                </div>
                                <p class="mb-0 text-muted small">A special camp for property registration will be held at the regional office. All allottees who have completed 100% payment are requested to attend with original documents.</p>
                            </div>
                        </div>
                    </div>
                </div>

                                <!-- Recent Transactions -->
                <div class="card border-0 mb-4" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0 border-bottom">
                        <div class="d-flex justify-content-between align-items-center p-3 px-4 bg-light">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-receipt text-muted me-2"></i>Recent Transactions</h6>
                            <button class="btn btn-sm btn-outline-secondary py-0" onclick="App.loadStep(7, null)" style="font-size: 12px;">View All</button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if($allottee->allotteeTransaction && $allottee->allotteeTransaction->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="text-muted" style="font-size: 13px;">
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allottee->allotteeTransaction->sortByDesc('paid_at')->take(5) as $trx)
                                            <tr>
                                                <td class="fw-medium font-monospace small">{{ $trx->transaction_no ?? '-' }}</td>
                                                <td>{{ $trx->paid_at ? \Carbon\Carbon::parse($trx->paid_at)->format('d M Y') : '-' }}</td>
                                                <td>
                                                    @if($trx->transaction_type == 'emi')
                                                        <span class="badge bg-light text-dark border">EMI</span>
                                                    @else
                                                        <span class="badge bg-light text-dark border">{{ ucfirst($trx->transaction_type) }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-bold">₹ {{ number_format($trx->amount, 2) }}</td>
                                                <td class="text-center">
                                                    @if(strtolower($trx->payment_status) == 'success')
                                                        <span class="badge bg-success bg-opacity-10 text-success"><i class="fa-solid fa-check-circle me-1"></i> Success</span>
                                                    @elseif(strtolower($trx->payment_status) == 'failed')
                                                        <span class="badge bg-danger bg-opacity-10 text-danger"><i class="fa-solid fa-times-circle me-1"></i> Failed</span>
                                                    @else
                                                        <span class="badge bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-clock me-1"></i> Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted p-4">
                                <div class="fs-1 mb-3 opacity-25"><i class="fa-solid fa-receipt"></i></div>
                                <h6>No transactions found</h6>
                                <p class="small mb-0">You have not made any payments yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar / Extra Details -->
            <div class="col-lg-4">
                
                <!-- Upcoming Demand -->
                @if($nextEmi)
                <div class="card border-0 bg-danger bg-opacity-10 mb-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fa-solid fa-triangle-exclamation text-danger fs-4 me-2"></i>
                            <h5 class="mb-0 fw-bold text-danger">Payment Due!</h5>
                        </div>
                        <p class="text-danger opacity-75 mb-1 small">You have an upcoming or overdue payment.</p>
                        <h2 class="text-danger fw-bold mb-3">₹ {{ number_format($nextEmi->outstanding_amount ?? $nextEmi->total_demand_amount, 2) }}</h2>
                        
                        <div class="d-flex justify-content-between small text-danger fw-medium mb-3">
                            <span>Due Date:</span>
                            <span>{{ $nextEmi->due_date ? \Carbon\Carbon::parse($nextEmi->due_date)->format('d M Y') : 'N/A' }}</span>
                        </div>
                        
                        <button class="btn btn-danger w-100 fw-bold" onclick="App.loadStep(8, null)">Pay Now</button>
                    </div>
                </div>
                @endif

                                <!-- Calendar -->
                <link rel="stylesheet" href="{{ asset('css/calendar.css') }}">
                
                <div class="card border-0 mb-4" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px;" id="calendarApp">
                    <div class="card-body p-0 border-bottom">
                        <div class="d-flex justify-content-between align-items-center p-3 px-4 bg-light">
                            <h6 class="fw-bold mb-0 text-dark" style="display:flex; align-items:center;">
                                <i class="fa-regular fa-calendar-alt text-muted me-2"></i>
                                <span id="monthYearDisplay">January 2026</span>
                            </h6>
                            <div class="nav-buttons d-flex gap-2">
                                <button id="prevMonthBtn" class="btn btn-sm btn-outline-secondary py-0 px-2 border-0"><i class="fas fa-chevron-left" style="font-size: 10px;"></i></button>
                                <button id="nextMonthBtn" class="btn btn-sm btn-outline-secondary py-0 px-2 border-0"><i class="fas fa-chevron-right" style="font-size: 10px;"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 calendar-card">
                        <div class="weekdays">
                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span>
                            <span>Th</span><span>Fr</span><span>Sa</span>
                        </div>
                        <div class="days-grid" id="daysGrid"></div>
                    </div>
                </div>

                <!-- Contact & Bank Info -->
                <div class="card border-0 mb-4" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0 border-bottom">
                        <div class="d-flex justify-content-between align-items-center p-3 px-4 bg-light">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-address-card text-muted me-2"></i>Contact Details</h6>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-3 d-flex align-items-start">
                                <i class="fa-solid fa-phone text-muted mt-1 me-3"></i>
                                <div>
                                    <div class="fw-bold">{{ $mobile }}</div>
                                    <div class="text-muted" style="font-size: 11px;">Primary Phone</div>
                                </div>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="fa-solid fa-envelope text-muted mt-1 me-3"></i>
                                <div>
                                    <div class="fw-bold">{{ $email }}</div>
                                    <div class="text-muted" style="font-size: 11px;">Email Address</div>
                                </div>
                            </li>
                            <li class="d-flex align-items-start">
                                <i class="fa-solid fa-location-dot text-muted mt-1 me-3"></i>
                                <div>
                                    <div class="fw-bold text-truncate" style="max-width: 200px;" title="{{ $post_office }}, {{ $district }}">
                                        {{ $post_office }}, {{ $district }}
                                    </div>
                                    <div class="text-muted" style="font-size: 11px;">Communication Address</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

    @endif
</div>

<script src="{{ asset('js/calendar.js') }}"></script>