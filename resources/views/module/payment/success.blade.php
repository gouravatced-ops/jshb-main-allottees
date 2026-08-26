@extends('layouts.main')

@section('title', 'Payment Success | JSHB')

@section('content')
<main class="main-content">
    <div class="page-header text-center">
        <h1 class="page-title text-success">
            <i class="fa-solid fa-circle-check"></i> Payment Successful
        </h1>
        <p class="page-subtitle">
            Your payment has been successfully processed.
        </p>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="info-card text-center shadow-sm p-5">
                <div class="mb-4">
                    <i class="fa-solid fa-receipt text-success" style="font-size: 4rem;"></i>
                </div>
                <h3 class="mb-3">Transaction #{{ $transaction->transaction_no ?? 'N/A' }}</h3>
                <p class="text-muted mb-4">
                    Amount Paid: <strong>₹ {{ number_format($payment->paid_amount ?? 0, 2) }}</strong><br>
                    Date: <strong>{{ optional($payment->paid_date ?? now())->format('d-m-Y h:i A') }}</strong>
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('dashboard') }}" class="btn-brand" style="background:#fff; color:var(--brand); border:1px solid var(--brand);">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </a>
                    
                    @if(isset($transaction->receipt_path) && $transaction->receipt_path)
                        <a href="{{ route('media.document', ['path' => rtrim(config('app.doc_api_url', 'http://localhost/jshb-doc'), '/') . '/' . ltrim($transaction->receipt_path, '/')]) }}" target="_blank" class="btn-brand">
                            <i class="fa-solid fa-download"></i> Download Receipt
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
