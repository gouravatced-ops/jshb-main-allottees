{{-- resources/views/admin/allottee/sections/overview.blade.php --}}
<div>
    <div class="page-header">
        <div>
            <h1 class="page-title">Overview</h1>
            <p class="page-subtitle">Key information at a glance · Application
                {{ $allottee->application_no ?? '-' }}</p>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="info-card">
                <p class="info-card-label"><i class="fa-solid fa-layer-group me-1"></i>Scheme</p>
                <p class="info-card-value">{{ $allottee->scheme->scheme_name ?? '—' }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <p class="info-card-label"><i class="fa-solid fa-map-pin me-1"></i>Division / Sub Division</p>
                <p class="info-card-value">{{ $allottee->division->name ?? '—' }} /
                    {{ $allottee->subDivision->name ?? '—' }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <p class="info-card-label"><i class="fa-solid fa-tag me-1"></i>Category</p>
                <p class="info-card-value">{{ $allottee->propertyCategory->name ?? '—' }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <p class="info-card-label"><i class="fa-solid fa-house me-1"></i>Property Type</p>
                <p class="info-card-value">{{ $allottee->propertyType->name ?? '—' }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <p class="info-card-label"><i class="fa-solid fa-people-group me-1"></i>Quarter Type</p>
                <p class="info-card-value">
                    {{ $allottee->quarterType->quarter_code ?? '' ?: '—' }}-{{ $allottee->quarterType->quarter_name ?? '' }}
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <p class="info-card-label">
                    <i class="fa-solid fa-house me-1"></i> Property Number
                </p>
                <p class="info-card-value badge bg-success ms-2 text-white">
                    {{ $allottee->property_number ?: '—' }}
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <p class="info-card-label"><i class="fa-solid fa-hashtag me-1"></i>Application No.</p>
                <p class="info-card-value" style="font-family:'DM Mono',monospace;">
                    {{ $allottee->application_no ?? '—' }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <p class="info-card-label"><i class="fa-regular fa-calendar me-1"></i>Application Date</p>
                <p class="info-card-value">
                    {{ $allottee->application_day ?? '' }}/{{ $allottee->application_month ?? '' }}/{{ $allottee->application_year ?? '' }}
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <p class="info-card-label"><i class="fa-solid fa-user me-1"></i>Applicant Name</p>
                <p class="info-card-value">
                    {{ trim(($allottee->prefix ?? '') . ' ' . ($allottee->allottee_name ?? '') . ' ' . ($allottee->allottee_middle_name ?? '') . ' ' . ($allottee->allottee_surname ?? '')) }}
                </p>
            </div>
        </div>
    </div>
    <div class="section-title">
        <i class="fa-solid fa-file-circle-check me-2"></i>
        Document Records
    </div>
    <div class="letter-grid">
        @foreach ($allottee->generatedDocument as $key => $document)
            @php
                $isSigned = !empty($document->signed_file_path);
                $isGenerated = !$isSigned && !empty($document->file_path);
                $documentType = $document->document_type;

                $filePath = $isSigned ? $document->signed_file_path : $document->file_path;
                $statusLabel = '';
                $subTitle = '';
                if ($documentType == 'agreement') {
                    $statusLabel = 'Uploaded';
                    $subTitle = 'Document Uploaded By Admin';
                } else {
                    $statusLabel = $isSigned ? 'Signed' : 'Generated';
                    $subTitle = $isSigned ? 'Your signed document is available' : 'Signed Document Uploded';
                }
            @endphp

            <div class="letter-hero letter-hero{{ $key + 1 }}">

                <p class="letter-hero-title">
                    <i class="fa-solid fa-envelope-open-text me-2"></i>

                    {{ ucwords($document->document_name) }}

                    <span class="badge bg-dark ms-2">
                        {{ $statusLabel }}
                    </span>
                </p>

                <p class="letter-hero-sub">
                    {{ $subTitle }}
                </p>

                <div class="app-no">
                    {{ $allottee->application_no ?? 'N/A' }}
                </div>

                <div
                    style="
                    display:flex;
                    gap:10px;
                    margin-top:18px;
                    flex-wrap:nowrap;
                ">

                    <!-- VIEW -->
                    <a href="{{ asset($filePath) }}" target="_blank" class="btn-brand"
                        style="
                            background:rgba(255,255,255,.2);
                            border:1.5px solid rgba(255,255,255,.4)
                        ">
                        <i class="fa-solid fa-eye"></i>
                        View
                    </a>

                    <!-- DOWNLOAD -->
                    <a href="{{ asset($filePath) }}" download class="btn-brand"
                        style="
                    background:rgba(255,255,255,.95);
                    color:var(--brand)
                ">
                        <i class="fa-solid fa-download"></i>
                        Download
                    </a>

                </div>
            </div>
        @endforeach
    </div>
</div>
