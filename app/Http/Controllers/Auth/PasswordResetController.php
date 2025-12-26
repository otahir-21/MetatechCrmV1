<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\PasswordResetSubmitRequest;
use App\Mail\PasswordResetMail;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Show password reset request form.
     *
     * @return \Illuminate\View\View
     */
    public function showRequestForm()
    {
        return view('auth.password.request');
    }

    /**
     * Handle password reset request.
     *
     * @param PasswordResetRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestReset(PasswordResetRequest $request)
    {
        try {
            $resetToken = $this->passwordResetService->createResetToken(
                $request->email,
                $request->ip()
            );

            // Send reset email with plain token
            Mail::to($request->email)->send(
                new PasswordResetMail($resetToken->user, $resetToken->plain_token)
            );

            return back()->with('status', 'If that email exists, a password reset link has been sent.');
        } catch (\Exception $e) {
            // Don't reveal if email exists (security best practice)
            if ($e->getCode() === 200) {
                return back()->with('status', $e->getMessage());
            }

            if ($e->getCode() === 403) {
                return back()->withErrors(['email' => $e->getMessage()]);
            }

            return back()->with('status', 'If that email exists, a password reset link has been sent.');
        }
    }

    /**
     * Show password reset form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');

        if (!$email || !$token) {
            return redirect()->route('password.request')
                ->withErrors(['token' => 'Invalid reset link.']);
        }

        // Verify token is valid
        $resetToken = $this->passwordResetService->verifyToken($email, $token);
        if (!$resetToken) {
            return redirect()->route('password.request')
                ->withErrors(['token' => 'This password reset link is invalid or has expired.']);
        }

        return view('auth.password.reset', [
            'email' => $email,
            'token' => $token,
        ]);
    }

    /**
     * Handle password reset submission.
     *
     * @param PasswordResetSubmitRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(PasswordResetSubmitRequest $request)
    {
        try {
            $this->passwordResetService->resetPassword(
                $request->email,
                $request->token,
                $request->password
            );

            return redirect()->route('login')
                ->with('status', 'Your password has been reset successfully. You can now login with your new password.');
        } catch (\Exception $e) {
            return back()->withErrors(['token' => $e->getMessage()]);
        }
    }
}
