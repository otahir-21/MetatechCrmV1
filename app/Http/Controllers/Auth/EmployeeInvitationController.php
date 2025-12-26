<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeInvitationAcceptRequest;
use App\Services\EmployeeInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeInvitationController extends Controller
{
    protected EmployeeInvitationService $invitationService;

    public function __construct(EmployeeInvitationService $invitationService)
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

        if (!$email || !$token) {
            return redirect()->route('login')
                ->withErrors(['invitation' => 'Invalid invitation link.']);
        }

        // Verify invitation is valid
        $invitation = $this->invitationService->verifyInvitation($email, $token);
        if (!$invitation) {
            return redirect()->route('login')
                ->withErrors(['invitation' => 'This invitation link is invalid or has expired.']);
        }

        return view('auth.employee-invite.accept', [
            'email' => $email,
            'token' => $token,
            'invitation' => $invitation,
        ]);
    }

    /**
     * Handle invitation acceptance.
     *
     * @param EmployeeInvitationAcceptRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acceptInvitation(EmployeeInvitationAcceptRequest $request)
    {
        try {
            $user = $this->invitationService->acceptInvitation(
                $request->email,
                $request->token,
                $request->validated()
            );

            // Automatically log in the user
            Auth::login($user);

            return redirect()->route('internal.dashboard')
                ->with('status', 'Your account has been activated successfully! Welcome to Metatech Internal CRM.');
        } catch (\Exception $e) {
            return back()->withErrors(['invitation' => $e->getMessage()])->withInput();
        }
    }
}
