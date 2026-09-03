<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>झारखण्ड राज्य आवास बोर्ड - EMI Statement</title>
    <style>
        * {
            box-sizing: border-box;
        }

        @font-face {
            font-family: 'KrutiDev';
            src: url("{{ public_path('font/KrutiDev010.ttf') }}") format('truetype');
        }

        @page {
            margin: 45px 40px;
        }

        .hindi-text {
            font-family: 'KrutiDev', sans-serif;
            font-size: 18px;
            line-height: 1.7;
            font-weight: normal;
            letter-spacing: 0;
            word-spacing: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            color: #1a1a2e;
            line-height: 1.35;
        }

        /* A4 page - exact print dimensions */
        .page {
            width: 100%;
            background: white;
            margin: 0 auto;
            position: relative;
        }

        .footer-section {
            margin-top: auto;
        }

        .watermark {
            position: fixed;
            top: 30%;
            left: 15%;
            opacity: 0.08;
            z-index: -1;
        }

        .watermark img {
            width: 450px;
        }

        .action-bar {
            max-width: 21cm;
            width: 100%;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-print {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-print:hover {
            background-color: #1d4ed8;
        }

        /* Mobile view adjustments */
        @media screen and (max-width: 768px) {
            body {
                padding: 10px;
            }

            .page {
                padding: 15px;
                min-height: auto;
            }

            .action-bar {
                justify-content: center;
            }
        }

        /* Print specific - ensure clean page breaks */
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .page {
                margin: 0;
                padding: 1cm;
                page-break-after: always;
                box-shadow: none;
                /* Keep min-height to ensure flex layout pushes footer to bottom on short pages */
                min-height: 29.7cm;
            }

            @page {
                size: A4;
                margin: 0;
                /* Let .page padding handle margins */
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body style="font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 0; padding: 0.825cm 1cm; color: #1a1a2e; background: #fff; line-height: 1.35;">

    <div class="page">
        <div class="watermark no-print-hide">
            <img src="{{ public_path('img/jshb_logo.png') }}" alt="Watermark">
        </div>
        {{-- ================= HEADER SECTION ================= --}}
        <table style="width: 100%; border-bottom: 2px solid #0f4c5f; padding-bottom: 6px; margin-bottom: 10px;">
            <tr>
                <td style="width: 70%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 55px;">
                                <img src="{{ public_path('img/jshb_logo.png') }}"
                                    style="width: 50px; height: 50px; object-fit: contain;" alt="Logo">
                            </td>
                            <td>
                                <div style="font-size: 14px; font-weight: bold; color: #0f4c5f;">Jharkhand State Housing
                                    Board</div>
                                <div style="font-size: 14px; color: #555;"><span class="hindi-text">>kj[k.M jkT; vkokl cksM</span> <span>| Housing for All</span> </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%; text-align: right;">
                    <div
                        style="font-size: 16px; font-weight: bold; color: #2563eb; background: #eff6ff; padding: 2px 10px; border-radius: 25px; display: inline-block;">
                        STATEMENT</div>
                    <div style="font-size: 9px; color: #777;">EMI Account Statement</div>
                </td>
            </tr>
        </table>

        {{-- ================= META CARD ================= --}}
        <table
            style="width: 100%; background: #f8fafc; border-left: 4px solid #0f4c5f; padding: 6px 10px; margin-bottom: 12px; border-radius: 6px;">
            <tr>
                <td style="width: 50%;">
                    <div style="font-size: 9px; color: #5a6e7a; letter-spacing: 0.5px;">ACCOUNT STATUS</div>
                    <div style="font-size: 14px; font-weight: bold; color: #0f4c5f; text-transform: uppercase;">
                        {{ $emiAccount->account_status == 'closed' ? 'Closed' : 'Active' }}
                    </div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div style="font-size: 9px; color: #5a6e7a;">STATEMENT DATE</div>
                    <div style="font-size: 12px; font-weight: bold; color: #0f4c5f;">
                        {{ now()->format('d-m-Y') }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- ================= APPLICANT INFO (COMPACT GRID) ================= --}}
        <table style="width: 100%; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 12px; padding: 6px;">
            <tr>
                <td style="width: 50%; padding: 4px 6px;">
                    <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">Application No.</div>
                    <div style="font-size: 11px; font-weight: 600;">{{ $allottee->application_no ?? '—' }}</div>
                </td>
                <td style="width: 50%; padding: 4px 6px;">
                    <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">Allotment No.</div>
                    <div style="font-size: 11px; font-weight: 600;">{{ $allottee->allotment_no ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td style="padding: 4px 6px;">
                    <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">Applicant Name</div>
                    <div style="font-size: 11px; font-weight: 600;">
                        {{ trim(
                            ($allottee->prefix ?? '') .
                                ' ' .
                                ($allottee->allottee_name ?? '') .
                                ' ' .
                                ($allottee->allottee_middle_name ?? '') .
                                ' ' .
                                ($allottee->allottee_surname ?? ''),
                        ) ?:
                            '—' }}
                    </div>
                </td>
                <td style="padding: 4px 6px;">
                    <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">Property No.</div>
                    <div style="font-size: 11px; font-weight: 600;">{{ $allottee->property_number ?? '—' }}</div>
                </td>
            </tr>
        </table>

        {{-- ================= ACCOUNT SUMMARY ================= --}}
        <div style="margin: 8px 0 5px 0;">
            <span
                style="font-size: 12px; font-weight: bold; color: #0f4c5f; border-left: 3px solid #2563eb; padding-left: 6px;">Account Summary</span>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
            <tr>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb; width: 25%; font-size: 9px;">Principal Amount</td>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; font-weight: 600; width: 25%;">Rs. {{ number_format($emiAccount->principal_amount, 2) }}</td>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb; width: 25%; font-size: 9px;">Total EMI (Months)</td>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; font-weight: 600; width: 25%;">{{ $emiAccount->tenure_months }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb; font-size: 9px;">EMI Amount (Monthly)</td>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; font-weight: 600; color: #0f4c5f;">Rs. {{ number_format($emiAccount->emi_amount, 2) }}</td>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb; font-size: 9px;">Interest Rate</td>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; font-weight: 600;">{{ $emiAccount->annual_interest_rate }}%</td>
            </tr>
            <tr>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb; font-size: 9px;">Total Paid Amount</td>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; font-weight: 600; color: #15803d;">Rs. {{ number_format($emiAccount->paid_amount, 2) }}</td>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb; font-size: 9px;">Balance Amount</td>
                <td style="border: 1px solid #e2e8f0; padding: 6px 8px; font-weight: 600; color: #b91c1c;">Rs. {{ number_format($emiAccount->remaining_amount, 2) }}</td>
            </tr>
        </table>

        {{-- ================= EMI SCHEDULE TABLE ================= --}}
        <div style="margin: 8px 0 5px 0;">
            <span
                style="font-size: 12px; font-weight: bold; color: #0f4c5f; border-left: 3px solid #2563eb; padding-left: 6px;">EMI Schedule</span>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px;">
            <thead>
                <tr style="background: #f9fafb; font-weight: bold; color: #0f4c5f;">
                    <td style="border: 1px solid #e2e8f0; padding: 4px;">#</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px;">Due Date</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: right;">Opening Bal.</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: right;">EMI Amount</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: right;">Penalty</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: right;">Total Paid</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: center;">Status</td>
                </tr>
            </thead>
            <tbody>
                @forelse($demands as $demand)
                <tr>
                    <td style="border: 1px solid #e2e8f0; padding: 4px;">{{ $demand->emi_no }}</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px;">{{ \Carbon\Carbon::parse($demand->due_date)->format('d-m-Y') }}</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: right;">Rs.{{ number_format($demand->opening_balance, 2) }}</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: right;">Rs.{{ number_format($demand->annualized_amount, 2) }}</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: right;">Rs.{{ number_format($demand->late_fine_penalty + $demand->penalty_interest_amount, 2) }}</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: right;">Rs.{{ number_format($demand->total_paid_amount, 2) }}</td>
                    <td style="border: 1px solid #e2e8f0; padding: 4px; text-align: center;">
                        @if ($demand->demand_status == 'Paid')
                        <span style="color: green; font-weight: bold;">Paid</span>
                        @elseif($demand->demand_status == 'Overdue')
                        <span style="color: red; font-weight: bold;">Overdue</span>
                        @elseif($demand->demand_status == 'Partially Paid')
                        <span style="color: #0284c7; font-weight: bold;">Partial</span>
                        @else
                        <span style="color: #b45309; font-weight: bold;">Pending</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="border: 1px solid #e2e8f0; padding: 4px; text-align: center;">No EMI Demands Found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- ================= SIGNATURE & STAMP (COMPACT) ================= --}}
        <div class="footer-section">
            <table style="width: 100%; margin-top: 24px;">
                <tr>
                    <td style="width: 55%;"></td>
                    <td style="width: 45%; text-align: center;">
                        <div style="width: 120px; border-top: 1px solid #333; margin: 0 auto 3px auto;"></div>
                        <div style="font-size: 9px;">Authorized Signatory</div>
                        <div style="font-size: 8px; color: #777;">JSHB</div>
                    </td>
                </tr>
            </table>

            {{-- ================= FOOTER ================= --}}
            <div
                style="margin-top: 12px; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 6px;">
                This is a system generated statement – valid without signature. | For any query contact: helpdesk@jshb.in | Date: {{ now()->format('d-m-Y h:i A') }}
            </div>
        </div>
    </div>

    <script>
        // Disable Right Click
        document.addEventListener('contextmenu', event => event.preventDefault());

        // Disable Keyboard Shortcuts for Inspect Element
        document.onkeydown = function(e) {
                if (e.keyCode === 123) { // F12
                    <
                    /body>

                    <
                    /html>
