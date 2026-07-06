<?php

namespace App\Http\Controllers;

use App\Models\OtpLog;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ForgotPasswordController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'captcha' => ['required', 'captcha'],
        ], [
            'captcha.captcha' => 'Invalid captcha. Please try again.',
            'captcha.required' => 'The captcha field is required.'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        $this->otpService->generateAndSendOtp(
            $user->id,
            $user->email,
            'password_reset',
            'Your OTP for password reset is:',
            $request->ip(),
            $request->userAgent()
        );

        session([
            'password_reset_email' => $user->email,
            'password_reset_otp_required' => true,
        ]);

        /*
        $status = Password::sendResetLink(
            $request->only('email')
        );
        */

        return back()
            ->with('success', 'OTP generated successfully. Enter the OTP below to verify and reset your password.')
            ->with('otp_required', true)
            ->with('email', $user->email);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp_code' => ['required', 'digits:6'],
            'captcha' => ['required', 'captcha'],
        ], [
            'captcha.captcha' => 'Invalid captcha. Please try again.',
            'captcha.required' => 'The captcha field is required.'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        $isValid = $this->otpService->verifyOtp($user->id, $request->otp_code, 'password_reset');

        if (! $isValid) {
            return back()
                ->withInput()
                ->with('otp_required', true)
                ->with('email', $request->email)
                ->withErrors(['otp_code' => 'Invalid OTP or expired. Please try again.']);
        }

        session([
            'password_reset_verified_email' => $user->email,
            'password_reset_verified_at' => now()->toDateTimeString(),
        ]);

        return redirect()
            ->route('password.reset')
            ->with('success', 'OTP verified successfully. You can now set a new password.');
    }

    public function showResetForm(Request $request)
    {
        $email = session('password_reset_verified_email');

        if (! $email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Please verify OTP first to reset your password.']);
        }

        return view('auth.reset-password', [
            'email' => $email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $verifiedEmail = session('password_reset_verified_email');

        if (! $verifiedEmail || $verifiedEmail !== $request->email) {
            return redirect()->route('password.request')->withErrors(['email' => 'OTP verification is required before resetting the password.']);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput()->withErrors(['email' => 'We could not find a user with that email address.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'password_created_at' => now(),
        ])->save();

        session()->forget([
            'password_reset_email',
            'password_reset_otp_required',
            'password_reset_verified_email',
            'password_reset_verified_at',
        ]);

        return redirect()->route('login')->with('success', 'Password reset successfully. Please login with your new password.');
    }
}
