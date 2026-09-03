<div>
    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                Choose You Payment Option
            </h1>
            <p class="page-subtitle">
                Select your preferred payment method for property booking
            </p>
        </div>
    </div>


    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Payment Options Section --}}
    <div class="payment-plan-wrapper mt-4">

        <div class="row g-4">

            {{-- EMI PLAN --}}
            <div class="col-lg-6">

                <form method="POST"
                    action="{{ route('allottees.payment-option', encryptId($allottee->id)) }}">
                    @csrf
                    <input type="hidden"
                        name="payment_option"
                        value="emi">

                    <button type="submit"
                        class="payment-card payment-card-emi w-100 {{ $allottee->payment_option === 'emi' ? 'border border-2 border-success' : '' }}" 
                        {{ $allottee->payment_option ? 'disabled' : '' }}>

                        <div class="payment-icon">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>

                        <div class="payment-content">

                            <div class="payment-top">
                                <h4>EMI Plan</h4>

                                <!-- <span class="popular-badge">
                                    Popular
                                </span> -->
                            </div>

                            <p>
                                Pay the amount in flexible monthly installments
                                with better financial comfort.
                            </p>

                            <ul class="payment-features">
                                <li>
                                    <i class="fa-solid fa-check"></i>
                                    Easy Monthly Installments
                                </li>

                                <li>
                                    <i class="fa-solid fa-check"></i>
                                    Lower Initial Burden
                                </li>

                                <li>
                                    <i class="fa-solid fa-check"></i>
                                    Flexible Payment Schedule
                                </li>
                            </ul>

                            <div class="payment-action {{ $allottee->payment_option === 'emi' ? 'text-success fw-bold' : '' }}">
                                @if($allottee->payment_option === 'emi')
                                    <i class="fa-solid fa-check-circle me-2"></i> Selected Option
                                @else
                                    Choose EMI Plan
                                    <i class="fa-solid fa-arrow-right ms-2"></i>
                                @endif
                            </div>

                        </div>
                    </button>

                </form>
            </div>

            {{-- ONE TIME PAYMENT --}}
            <div class="col-lg-6">

                <form method="POST"
                    action="{{ route('allottees.payment-option', encryptId($allottee->id)) }}">
                    @csrf
                    <input type="hidden"
                        name="payment_option"
                        value="one_time">

                    <button type="submit"
                        class="payment-card payment-card-full w-100 {{ $allottee->payment_option === 'one_time' ? 'border border-2 border-success' : '' }}"
                        {{ $allottee->payment_option ? 'disabled' : '' }}>

                        <div class="payment-icon">
                            <i class="fa-solid fa-wallet"></i>
                        </div>

                        <div class="payment-content">

                            <div class="payment-top">
                                <h4>One Time Payment</h4>

                                <!-- <span class="save-badge">
                                    Best Value
                                </span> -->
                            </div>

                            <p>
                                Pay the full amount at once and enjoy faster
                                processing & additional benefits.
                            </p>

                            <ul class="payment-features">
                                <li>
                                    <i class="fa-solid fa-check"></i>
                                    Complete Payment Instantly
                                </li>

                                <li>
                                    <i class="fa-solid fa-check"></i>
                                    Faster Approval Process
                                </li>

                                <li>
                                    <i class="fa-solid fa-check"></i>
                                    Additional Payment Benefits
                                </li>
                            </ul>

                            <div class="payment-action {{ $allottee->payment_option === 'one_time' ? 'text-success fw-bold' : '' }}">
                                @if($allottee->payment_option === 'one_time')
                                    <i class="fa-solid fa-check-circle me-2"></i> Selected Option
                                @else
                                    Choose One Time Payment
                                    <i class="fa-solid fa-arrow-right ms-2"></i>
                                @endif
                            </div>

                        </div>
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>
