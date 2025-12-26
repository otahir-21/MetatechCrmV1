<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyOwnerInvitationAcceptRequest;
use App\Services\CompanyOwnerInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompanyOwnerInvitationViewController extends Controller
{
    protected CompanyOwnerInvitationService $invitationService;

    public function __construct(CompanyOwnerInvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * Show invitation acceptance form.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showAcceptForm(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');
        $subdomainParam = $request->query('subdomain'); // Optional subdomain parameter from URL

        if (!$email || !$token) {
            return redirect()->route('login')->withErrors(['error' => 'Invalid invitation link.']);
        }

        try {
            $invitation = $this->invitationService->verifyInvitation($email, $token);

            if (!$invitation) {
                return view('invitations.invalid', ['message' => 'Invalid or expired invitation link.']);
            }

            // Verify subdomain parameter matches invitation (if provided)
            if ($subdomainParam && $subdomainParam !== $invitation->subdomain) {
                Log::warning('Subdomain mismatch in invitation link', [
                    'email' => $email,
                    'provided_subdomain' => $subdomainParam,
                    'invitation_subdomain' => $invitation->subdomain
                ]);
                return view('invitations.invalid', ['message' => 'Invalid invitation link.']);
            }

            return view('company-invite.accept', compact('email', 'token', 'invitation'));
        } catch (\Exception $e) {
            Log::error('Error showing company owner invitation acceptance form: ' . $e->getMessage(), ['email' => $email, 'token' => $token]);
            return view('invitations.invalid', ['message' => 'An error occurred while verifying your invitation.']);
        }
    }

    /**
     * Accept company owner invitation and set password.
     *
     * @param CompanyOwnerInvitationAcceptRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acceptInvitation(CompanyOwnerInvitationAcceptRequest $request)
    {
        try {
            $user = $this->invitationService->acceptInvitation(
                $request->email,
                $request->token,
                $request->validated()
            );

            // Get login URL for redirect
            $loginUrl = $this->invitationService->getLoginUrl($user->subdomain);

            return redirect($loginUrl)->with('status', 'Your account has been activated! You can now login to access your company portal.');
        } catch (\Exception $e) {
            Log::error('Error accepting company owner invitation: ' . $e->getMessage(), ['email' => $request->email, 'token' => $request->token]);
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }
}
