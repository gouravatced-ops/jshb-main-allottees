<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>झारखण्ड राज्य आवास बोर्ड - EMI भुगतान रसीद </title>
    <style>
        @font-face {
            font-family: 'KrutiDev';
            src: url("{{ public_path('font/KrutiDev010.ttf') }}") format('truetype');
        }

        .hindi-text {
            font-family: 'KrutiDev', sans-serif;
            font-size: 18px;
            line-height: 1.7;
            font-weight: normal;
            letter-spacing: 0;
            word-spacing: 0;
        }
    </style>
</head>

<body
    style="font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 0; padding: 12px; color: #1a1a2e; background: #fff; line-height: 1.35;">

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
                            <div style="font-size: 14px; color: #555;"><span class="hindi-text">>kj[k.M jkT; vkokl
                                    cksMZ</span> <span>| Housing for All</span> </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 30%; text-align: right;">
                <div
                    style="font-size: 16px; font-weight: bold; color: #e67e22; background: #fef5e8; padding: 2px 10px; border-radius: 25px; display: inline-block;">
                    PAID</div>
                <div style="font-size: 9px; color: #777;">EMI Payment Receipt</div>
            </td>
        </tr>
    </table>

    @php
        $dueDate = \Carbon\Carbon::parse($demand->due_date);
        $paidDate = $transaction->paid_at ? \Carbon\Carbon::parse($transaction->paid_at) : null;
        $isLate = false;
        $lateText = '';

        if ($paidDate && $paidDate->startOfDay()->gt($dueDate->startOfDay())) {
            $isLate = true;
            $diff = $dueDate->startOfDay()->diff($paidDate->startOfDay());
            
            $months = $diff->m + ($diff->y * 12);
            $days = $diff->d;
            
            $parts = [];
            if ($months > 0) $parts[] = $months . ' Month' . ($months > 1 ? 's' : '');
            if ($days > 0) $parts[] = $days . ' Day' . ($days > 1 ? 's' : '');
            
            $lateText = implode(', ', $parts) . ' Late';
        } elseif (($demand->late_fine_penalty ?? 0) > 0 || ($demand->penalty_interest_amount ?? 0) > 0) {
            $isLate = true;
        }
    @endphp

    {{-- ================= TRANSACTION CARD ================= --}}
    <table
        style="width: 100%; background: #f8fafc; border-left: 4px solid #0f4c5f; padding: 6px 10px; margin-bottom: 12px; border-radius: 6px;">
        <tr>
            <td style="width: 35%;">
                <div style="font-size: 9px; color: #5a6e7a; letter-spacing: 0.5px;">TRANSACTION NUMBER</div>
                <div style="font-size: 13px; font-weight: bold; color: #0f4c5f; font-family: monospace;">
                    {{ $transaction->transaction_no ?? '-' }}
                </div>
            </td>
            <td style="width: 20%; text-align: center;">
                <div style="font-size: 9px; color: #5a6e7a; letter-spacing: 0.5px;">DUE DATE</div>
                <div style="font-size: 11px; font-weight: bold; color: #0f4c5f;">
                    {{ $dueDate->format('d-m-Y') }}
                </div>
            </td>
            <td style="width: 25%; text-align: center;">
                <div style="font-size: 9px; color: #5a6e7a; letter-spacing: 0.5px;">PAYMENT DATE</div>
                <div style="font-size: 11px; font-weight: bold; color: #0f4c5f;">
                    {{ $paidDate ? $paidDate->format('d-m-Y h:i A') : 'N/A' }}
                </div>
            </td>
            <td style="width: 20%; text-align: right;">
                <div style="font-size: 9px; color: #5a6e7a; letter-spacing: 0.5px;">STATUS</div>
                <div style="font-size: 12px; font-weight: bold; color: {{ $isLate ? '#dc2626' : '#16a34a' }};">
                    {{ $isLate ? 'PAID LATE' : 'PAID ON TIME' }}
                </div>
                @if($isLate && $lateText)
                <div style="font-size: 8.5px; color: #dc2626; margin-top: 2px;">
                    ({{ $lateText }})
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ================= APPLICANT INFO (COMPACT GRID) ================= --}}
    <table style="width: 100%; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 12px; padding: 6px;">
        <tr>
            <td style="width: 50%; padding: 4px 6px;">
                <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">Application No.</div>
                <div style="font-size: 11px; font-weight: 600;">{{ $demand->allottee->application_no ?? '—' }}</div>
            </td>
            <td style="width: 50%; padding: 4px 6px;">
                <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">Allotment No.</div>
                <div style="font-size: 11px; font-weight: 600;">{{ $demand->allottee->allotment_no ?? '—' }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding: 4px 6px;">
                <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">Applicant Name</div>
                <div style="font-size: 11px; font-weight: 600;">
                    {{ trim(
                        ($demand->allottee->prefix ?? '') .
                            ' ' .
                            ($demand->allottee->allottee_name ?? '') .
                            ' ' .
                            ($demand->allottee->allottee_middle_name ?? '') .
                            ' ' .
                            ($demand->allottee->allottee_surname ?? ''),
                    ) ?:
                        '—' }}
                </div>
            </td>
            <td style="padding: 4px 6px;">
                <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">Payment Mode</div>
                <div style="font-size: 11px; font-weight: 600;">{{ strtoupper($transaction->payment_mode ?? '—') }} {{ $transaction->utr_no ? '[' . $transaction->utr_no . ']' : '' }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding: 4px 6px;">
                <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">Property No.</div>
                <div style="font-size: 11px; font-weight: 600;">{{ $demand->allottee->property_number ?? '—' }}</div>
            </td>
            <td style="padding: 4px 6px;">
                <div style="font-size: 8px; color: #5f7f9c; text-transform: uppercase;">EMI Month</div>
                <div style="font-size: 11px; font-weight: 600;">{{ $emiMonth ?? '—' }} (Installment No: {{ $demand->emi_no ?? '—' }})</div>
            </td>
        </tr>
    </table>

    {{-- ================= PAYMENT SUMMARY TABLE ================= --}}
    <div style="margin: 8px 0 5px 0;">
        <span
            style="font-size: 12px; font-weight: bold; color: #0f4c5f; border-left: 3px solid #e67e22; padding-left: 6px;">Payment
            Breakdown</span>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb; width: 60%;">Monthly Installment (Principal + Interest)
            </td>
            <td style="border: 1px solid #e2e8f0; padding: 6px 8px; text-align: right; font-weight: 600;">₹
                {{ number_format($demand->emi_amount, 2) }}
            </td>
        </tr>
        @if (($demand->late_fine_penalty ?? 0) > 0)
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb;">Late Fine Penalty (1% of EMI)</td>
            <td style="border: 1px solid #e2e8f0; padding: 6px 8px; text-align: right; font-weight: 600;">₹
                {{ number_format($demand->late_fine_penalty, 2) }}
            </td>
        </tr>
        @endif
        @if (($demand->penalty_interest_amount ?? 0) > 0)
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb;">Penalty Interest (16% Annual Rate)</td>
            <td style="border: 1px solid #e2e8f0; padding: 6px 8px; text-align: right; font-weight: 600;">₹
                {{ number_format($demand->penalty_interest_amount, 2) }}
            </td>
        </tr>
        @endif
        @if (($demand->penalty_admin_charges ?? 0) > 0)
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 6px 8px; background: #f9fafb;">Administrative Charges</td>
            <td style="border: 1px solid #e2e8f0; padding: 6px 8px; text-align: right; font-weight: 600;">₹
                {{ number_format($demand->penalty_admin_charges, 2) }}
            </td>
        </tr>
        @endif
        <tr style="background: #fef5e8;">
            <td style="border: 1px solid #e2e8f0; padding: 8px; font-weight: bold; font-size: 12px;">TOTAL RECEIVED AMOUNT
            </td>
            <td
                style="border: 1px solid #e2e8f0; padding: 8px; text-align: right; font-weight: bold; font-size: 13px; color: #c2410c;">
                ₹ {{ number_format($transaction->total_amount, 2) }}</td>
        </tr>
    </table>

    {{-- ================= AMOUNT IN WORDS (ENGLISH + HINDI) ================= --}}
    <div
        style="margin-top: 6px; padding: 6px 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; font-size: 9px; line-height: 1.4;">
        <div><strong style="color: #0f4c5f;">Amount in Words (English):</strong> {{ ucfirst($amountInEnglish ?? '') }}
        </div>
    </div>

    {{-- ================= SIGNATURE & STAMP (COMPACT) ================= --}}
    <table style="width: 100%; margin-top: 14px;">
        <tr>
            <td style="width: 45%; text-align: center;">
                <div style="width: 120px; border-top: 1px solid #333; margin: 0 auto 3px auto;"></div>
                <div style="font-size: 9px;">Authorized Signatory</div>
                <div style="font-size: 8px; color: #777;">JSHB</div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%; text-align: center;">
                <div style="width: 120px; border-top: 1px solid #333; margin: 0 auto 3px auto;"></div>
                <div style="font-size: 9px;">Allottee Signature</div>
                <div style="font-size: 8px; color: #777;">(On original copy)</div>
            </td>
        </tr>
    </table>

    {{-- ================= FOOTER ================= --}}
    <div
        style="margin-top: 12px; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 6px;">
        This is a system generated receipt – valid without signature. | For any query contact: helpdesk@jshb.in
    </div>

</body>

</html>
