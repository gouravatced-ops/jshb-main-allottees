@extends('layouts.main')

@section('title', 'Allottee Dashboard | JSHB')

@section('content')

    {{-- DYNAMIC CONTENT WRAPPER --}}
    <main class="main-content">
        <div id="dynamicContent">
            {{-- Dynamically load the requested blade section --}}
            @php
                // If $blade is not passed, default to 'overview'
                $activeBlade = $blade ?? 'overview';
                $bladePath = 'module.sections.' . $activeBlade;
            @endphp
            
            @if (view()->exists($bladePath))
                @include($bladePath, ['allottee' => $allottee, 'step' => $step ?? null])
            @else
                <div class="alert alert-info">Dashboard content loading...</div>
            @endif
        </div>
    </main>

    <script>
        window.AppConfig = {
            routes: {
                overview: '{{ route('dashboard') }}',
                process: '{{ url('/') }}/step-__STEP__',
                initialPayment: '{{ route('allottee.initial-payment.pay') }}',
                oneTimePayment: '{{ route('allottee.one-time-payment.pay') }}',
                uploadSigned: '{{ route('allottee.signed.document.uploads') }}',
                emiProcessPayment: '{{ isset($allottee) ? route('allottee.emi.process-payment', ['allottee' => encryptId($allottee->id)]) : '' }}',
            },
            currentStepNo: '{{ isset($step) ? $step->step_no : 1 }}'
        };
    </script>
@endsection


