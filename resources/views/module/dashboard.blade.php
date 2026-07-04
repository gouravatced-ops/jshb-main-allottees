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

@endsection


