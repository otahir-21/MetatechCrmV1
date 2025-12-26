<?php

namespace App\Mail;

use App\Models\EmployeeInvitation;
use App\Services\EmployeeInvitationService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public EmployeeInvitation $invitation;
    public string $token;
    public string $invitationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(EmployeeInvitation $invitation, string $token)
    {
        $this->invitation = $invitation;
        $this->token = $token;
        $this->invitationUrl = app(EmployeeInvitationService::class)->getInvitationUrl(
            $invitation->email,
            $token
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re Invited to Join Metatech Internal CRM',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.employee-invitation',
            with: [
                'invitation' => $this->invitation,
                'invitationUrl' => $this->invitationUrl,
                'token' => $this->token,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
