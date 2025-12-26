<?php

namespace App\Http\Controllers;

use App\Models\StaffInvitation;
use App\Services\StaffInvitationService;
use App\Http\Requests\StaffInvitationAcceptRequest;
use Illuminate\Http\Request;

class StaffInvitationViewController extends Controller
{
    protected StaffInvitationService $staffInvitationService;

    public function __construct(StaffInvitationService $staffInvitationService)
    {
        $this->staffInvitationService = $staffInvitationService;
    }

    /**
     * Show invitation acceptance form.
     */
    public function showAccept(string $token)
    {
        $invitation = StaffInvitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with(['company', 'invitedBy'])
            ->first();

        if (!$invitation) {
            return view('invitations.invalid', [
                'message' => 'Invitation not found, expired, or already accepted.',
            ]);
        }

        return view('invitations.accept', compact('invitation'));
    }

    /**
     * Handle invitation acceptance.
     */
    public function accept(StaffInvitationAcceptRequest $request, string $token)
    {
        try {
            $user = $this->staffInvitationService->acceptInvitation($token, $request->validated());

            return redirect()->route('login')->with('success', 'Invitation accepted! You can now login.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
