<?php
// app/Http/Controllers/Admin/AllotteePaymentController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllotteeGeneratedDocument;
use App\Models\AllotteePaymentOrder;
use App\Models\AllotteeTransaction;
use App\Models\AllotteeProcessStep;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Traits\DocumentUploadTrait;
use App\Services\NotificationService;
use App\Models\AllotteeLedger;
use NumberFormatter;

class AllotteePaymentController extends Controller
{
    use DocumentUploadTrait;

    public function payInitialPayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required'
        ]);
        DB::beginTransaction();
        try {
            // PAYMENT ORDER
            $payment = AllotteePaymentOrder::with(
                'allottee.scheme'
            )->findOrFail(
                $request->payment_id
            );

            // REFRESH PENALTY
            $payment->refreshPenalty();

            // TRANSACTION NUMBER
            $transactionNo =
                'TXN' .
                now()->format('YmdHis') .
                rand(1000, 9999);

            // UPDATE PAYMENT ORDER
            $payment->update([
                'paid_amount'      => $payment->total_payable,
                'remaining_amount' => 0,
                'paid_at'          => now(),
                'order_status'     => 'paid',
                'remarks'          =>
                'Payment received successfully',
            ]);

            // SAVE TRANSACTION
            $transaction =
                AllotteeTransaction::create([
                    'allottee_id'      => $payment->allottee_id,
                    'order_id'         => $payment->id,
                    'transaction_type' => 'allotment_payment',
                    'payment_stage'    => 'allotment',
                    'amount'           => $payment->total_payable,
                    'principal_amount' => $payment->base_amount,
                    'penalty_amount'   => $payment->penalty_amount,
                    'admin_charge'     => $payment->admin_charge,
                    'total_amount'     => $payment->total_payable,
                    'payment_mode'     => 'gateway',
                    'payment_status'   => 'success',
                    'transaction_no'   => $transactionNo,
                    'paid_at'          => now(),
                    'remarks'          =>
                    'Allotment payment successful',
                    'created_by'       => Auth::id(),
                ]);

            $payment->transaction_no  = $transaction->transaction_no;
            $payment->paid_date  = $transaction->paid_at;
            $payment->payment_gateway  = $transaction->payment_mode;

            // Amount in Word English and Hindi
            $amountInEnglish = amountToWords($payment->paid_amount, 'en');
            $amountInHindi = amountToWords($payment->paid_amount, 'hi');

            // GENERATE RECEIPT PDF
            $pdf = Pdf::loadView(
                'module.sections.initial-payment-receipt',
                compact(
                    'payment',
                    'transaction',
                    'amountInEnglish',
                    'amountInHindi'
                )
            )
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'dpi' => 96,
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                ]);

            // RECEIPT FOLDER
            $folder = implode('/', [
                'documents',
                'allottee',
                'payment',
                'allotment',
                now()->format('Y'),
                now()->format('m'),
                now()->format('d'),
            ]);
            $directory = public_path($folder);
            File::ensureDirectoryExists(
                $directory,
                0755,
                true
            );

            // RECEIPT FILE
            $fileName =
                'allotment-payment-receipt-' .
                $payment->id . '-' .
                now()->format('YmdHis') . '-' .
                rand(1000, 9999) .
                '.pdf';

            $pdfContent = $pdf->output();

            file_put_contents(
                $directory . '/' . $fileName,
                $pdfContent
            );

            // 2. Upload to Document API
            $allottee = $payment->allottee;
            $scheme = $allottee->scheme ?? null;
            $yyyy = date('Y');
            $mm = date('m');
            $dd = date('d');

            $extraData = [
                'application_for' => 'allotment',
                'division_code' => $allottee->division->division_code ?? '',
                'subdivision_code' => $allottee->subDivision->subdivision_code ?? '',
                'property_category' => $allottee->propertyCategory->category_code ?? '',
                'property_type' => $allottee->propertyType->type_code ?? '',
                'property_income' => $allottee->quarterType->quarter_code ?? '',
                'username' => $allottee->username ?? ''
            ];

            try {
                $uploadResult = $this->uploadContentToDocumentApi(
                    $pdfContent,
                    $fileName,
                    'FINAL',
                    $scheme->scheme_code ?? 'SCH',
                    $allottee->property_number ?? 'PROP',
                    $yyyy,
                    $mm,
                    $dd,
                    $extraData
                );
                $filePath = ltrim($uploadResult['file_path'], '/');
            } catch (\Exception $e) {
                Log::error('Document API Upload failed: ' . $e->getMessage());
                $filePath = $folder . '/' . $fileName;
            }

            // UPDATE TRANSACTION RECEIPT
            $transaction->update([
                'receipt_file' => $fileName,
                'receipt_path' => $filePath,
            ]);

            // SAVE GENERATED DOCUMENT
            AllotteeGeneratedDocument::create([
                'allottee_id'    =>
                $payment->allottee_id,
                'document_name'  =>
                'Allotment Payment Receipt',
                'document_type'  =>
                'allotment-payment-receipt',
                'file_name'      =>
                $fileName,
                'file_path'      =>
                $filePath,
                'generated_by'   =>
                Auth::id(),
                'generated_at'   =>
                now(),
            ]);

            // COMPLETE STEP
            AllotteeProcessStep::where([
                'allottee_id' =>
                $payment->allottee_id,
                'step_no'     => 6,
            ])->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'completed_by' => Auth::id(),
            ]);

            // UNLOCK NEXT STEP
            AllotteeProcessStep::where([
                'allottee_id' =>
                $payment->allottee_id,
                'step_no'     => 7,
            ])->update([
                'status' => 'pending',
            ]);

            // UPDATE ALLOTTEE LEDGER
            $lastLedger = AllotteeLedger::where('allottee_id', $payment->allottee_id)->orderBy('id', 'desc')->first();
            $runningBalance = ($lastLedger->running_balance ?? 0) - $payment->total_payable;

            AllotteeLedger::create([
                'allottee_id'      => $payment->allottee_id,
                'payment_id'       => $payment->id,
                'order_id'         => $payment->id,
                'transaction_date' => now(),
                'transaction_type' => 'allotment_payment',
                'transaction_mode' => 'gateway',
                'description'      => 'Allotment Payment Received',
                'debit_amount'     => 0,
                'credit_amount'    => $payment->total_payable,
                'running_principal' => 0,
                'running_balance'  => $runningBalance,
                'reference_no'     => $transactionNo,
                'remarks'          => 'Initial allotment payment',
                'created_by'       => Auth::id()
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' =>
                'Payment successful',
                'receipt_url' => asset(
                    $folder . '/' . $fileName
                ),
                'redirect' => route(
                    'modules.payment.success',
                    $payment->id
                )
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(
                'Allotment payment failed',
                [
                    'payment_id' => $request->payment_id,
                    'error'      => $e->getMessage(),
                    'line'       => $e->getLine(),
                    'file'       => $e->getFile(),
                ]
            );
            return response()->json([
                'success' => false,
                'message' =>
                'Payment failed',
                'error' =>
                $e->getMessage()
            ], 500);
        }
    }

    public function payOnetimePayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required'
        ]);
        $paymentId = base64_decode($request->payment_id, true);
        DB::beginTransaction();
        try {
            // PAYMENT ORDER
            $payment = AllotteePaymentOrder::with(
                'allottee.scheme'
            )->findOrFail(
                $paymentId
            );

            // REFRESH PENALTY
            $payment->refreshPenalty();

            // TRANSACTION NUMBER
            $transactionNo =
                'TXN' .
                now()->format('YmdHis') .
                rand(1000, 9999);

            // UPDATE PAYMENT ORDER
            $payment->update([
                'paid_amount'      => $payment->total_payable,
                'remaining_amount' => 0,
                'paid_at'          => now(),
                'order_status'     => 'paid',
                'remarks'          =>
                'Payment received successfully',
            ]);

            // SAVE TRANSACTION
            $transaction =
                AllotteeTransaction::create([
                    'allottee_id'      => $payment->allottee_id,
                    'order_id'         => $payment->id,
                    'transaction_type' => 'one_time_payment',
                    'payment_stage'    => 'closure',
                    'amount'           => $payment->total_payable,
                    'principal_amount' => $payment->base_amount,
                    'penalty_amount'   => $payment->penalty_amount,
                    'admin_charge'     => $payment->admin_charge,
                    'total_amount'     => $payment->total_payable,
                    'payment_mode'     => 'gateway',
                    'payment_status'   => 'success',
                    'transaction_no'   => $transactionNo,
                    'paid_at'          => now(),
                    'remarks'          =>
                    'Allotment payment successful',
                    'created_by'       => Auth::id(),
                ]);

            $payment->transaction_no  = $transaction->transaction_no;
            $payment->paid_date  = $transaction->paid_at;
            $payment->payment_gateway  = $transaction->payment_mode;

            // Amount in Word English and Hindi
            $amountInEnglish = amountToWords($payment->paid_amount, 'en');
            $amountInHindi = amountToWords($payment->paid_amount, 'hi');

            // GENERATE RECEIPT PDF
            $pdf = Pdf::loadView(
                'module.sections.initial-payment-receipt',
                compact(
                    'payment',
                    'transaction',
                    'amountInEnglish',
                    'amountInHindi'
                )
            )
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'dpi' => 96,
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                ]);

            // RECEIPT FOLDER
            $folder = implode('/', [
                'documents',
                'allottee',
                'payment',
                'one-time-payment',
                now()->format('Y'),
                now()->format('m'),
                now()->format('d'),
            ]);
            $directory = public_path($folder);
            File::ensureDirectoryExists(
                $directory,
                0755,
                true
            );

            // RECEIPT FILE
            $fileName =
                'one-time-payment-receipt-' .
                $payment->id . '-' .
                now()->format('YmdHis') . '-' .
                rand(1000, 9999) .
                '.pdf';

            $pdfContent = $pdf->output();

            file_put_contents(
                $directory . '/' . $fileName,
                $pdfContent
            );

            // 2. Upload to Document API
            $allottee = $payment->allottee;
            $scheme = $allottee->scheme ?? null;
            $yyyy = date('Y');
            $mm = date('m');
            $dd = date('d');

            $extraData = [
                'application_for' => 'one_time_payment',
                'division_code' => $allottee->division->division_code ?? '',
                'subdivision_code' => $allottee->subDivision->subdivision_code ?? '',
                'property_category' => $allottee->propertyCategory->category_code ?? '',
                'property_type' => $allottee->propertyType->type_code ?? '',
                'property_income' => $allottee->quarterType->quarter_code ?? '',
                'username' => $allottee->username ?? ''
            ];

            try {
                $uploadResult = $this->uploadContentToDocumentApi(
                    $pdfContent,
                    $fileName,
                    'FINAL',
                    $scheme->scheme_code ?? 'SCH',
                    $allottee->property_number ?? 'PROP',
                    $yyyy,
                    $mm,
                    $dd,
                    $extraData
                );
                $filePath = ltrim($uploadResult['file_path'], '/');
            } catch (\Exception $e) {
                Log::error('Document API Upload failed: ' . $e->getMessage());
                $filePath = $folder . '/' . $fileName;
            }

            // UPDATE TRANSACTION RECEIPT
            $transaction->update([
                'receipt_file' => $fileName,
                'receipt_path' => $filePath,
            ]);

            // SAVE GENERATED DOCUMENT
            AllotteeGeneratedDocument::create([
                'allottee_id'    =>
                $payment->allottee_id,
                'document_name'  =>
                'One Time Payment Receipt',
                'document_type'  =>
                'one-time-payment-receipt',
                'file_name'      =>
                $fileName,
                'file_path'      =>
                $filePath,
                'generated_by'   =>
                Auth::id(),
                'generated_at'   =>
                now(),
            ]);

            // COMPLETE STEP
            $now = now();
            $userId = Auth::id();

            AllotteeProcessStep::where('allottee_id', $payment->allottee_id)
                ->whereIn('step_no', [10, 11])
                ->update([
                    'status'       => 'completed',
                    'completed_at' => $now,
                    'completed_by' => $userId,
                ]);

            // --- SEND NOTIFICATIONS ---
            try {
                $allotteeFullName = trim(($allottee->prefix ?? '') . ' ' . ($allottee->allottee_name ?? '') . ' ' . ($allottee->allottee_surname ?? ''));
                $amountPaid = number_format($payment->paid_amount, 2);
                $propertyNo = $allottee->property_number ?? 'N/A';

                // 1. To Allottee
                $msgAllottee = "Dear {$allotteeFullName},\n\n";
                $msgAllottee .= "We have successfully received your One-Time Payment.\n";
                $msgAllottee .= "Property No: {$propertyNo}\n";
                $msgAllottee .= "Amount Paid: ₹{$amountPaid}\n";
                $msgAllottee .= "Transaction No: {$transactionNo}\n\n";
                $msgAllottee .= "Regards,\nJharkhand State Housing Board";

                app(\App\Services\NotificationService::class)->send([
                    'user_id' => $allottee->user_id,
                    'notification_type' => 'success',
                    'subject' => "One Time Payment Successful - {$propertyNo}",
                    'message' => $msgAllottee,
                    'is_allottee' => true,
                ]);

                // 2. To System
                $msgSystem = "System Alert: One Time Payment Received\n\n";
                $msgSystem .= "Allottee Name: {$allotteeFullName}\n";
                $msgSystem .= "Property No: {$propertyNo}\n";
                $msgSystem .= "Amount Paid: ₹{$amountPaid}\n";
                $msgSystem .= "Transaction No: {$transactionNo}\n";
                $msgSystem .= "Payment Date: " . now()->format('d M Y h:i A') . "\n";

                app(\App\Services\NotificationService::class)->send([
                    'email_id' => env('MAIL_SYSTEM_USERNAME', 'system@adms.jshb.computered.co.in'),
                    'notification_type' => 'info',
                    'subject' => "One Time Payment Received - {$propertyNo}",
                    'message' => $msgSystem,
                    'is_allottee' => false,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send one-time payment notification: ' . $e->getMessage());
            }
            // --- END NOTIFICATIONS ---

            // UPDATE ALLOTTEE LEDGER
            $lastLedger = AllotteeLedger::where('allottee_id', $payment->allottee_id)->orderBy('id', 'desc')->first();
            $runningBalance = ($lastLedger->running_balance ?? 0) - $payment->total_payable;

            AllotteeLedger::create([
                'allottee_id'      => $payment->allottee_id,
                'payment_id'       => $payment->id,
                'order_id'         => $payment->id,
                'transaction_date' => now(),
                'transaction_type' => 'one_time_payment',
                'transaction_mode' => 'gateway',
                'description'      => 'One Time Payment Received',
                'debit_amount'     => 0,
                'credit_amount'    => $payment->total_payable,
                'running_principal' => 0,
                'running_balance'  => $runningBalance,
                'reference_no'     => $transactionNo,
                'remarks'          => 'One time clearance payment',
                'created_by'       => Auth::id()
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' =>
                'Payment successful',
                'receipt_url' => asset(
                    $folder . '/' . $fileName
                ),
                'redirect' => route(
                    'modules.payment.success',
                    $payment->id
                )
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error(
                'Allotment payment failed',
                [
                    'payment_id' => $paymentId,
                    'error'      => $e->getMessage(),
                    'line'       => $e->getLine(),
                    'file'       => $e->getFile(),
                ]
            );
            return response()->json([
                'success' => false,
                'message' =>
                'Payment failed',
                'error' =>
                $e->getMessage()
            ], 500);
        }
    }

    public function paymentSuccess($id)
    {
        $payment = AllotteePaymentOrder::with('allottee')->findOrFail($id);
        $transaction = AllotteeTransaction::where('order_id', $id)->where('payment_status', 'success')->first();

        return view('module.payment.success', compact('payment', 'transaction'));
    }
}
