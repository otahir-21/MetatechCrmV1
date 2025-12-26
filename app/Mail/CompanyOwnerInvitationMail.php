<?php

namespace App\Mail;

use App\Models\CompanyOwnerInvitation;
use App\Services\CompanyOwnerInvitationService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyOwnerInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public CompanyOwnerInvitation $invitation;
    public string $token;
    public string $invitationUrl;
    public string $loginUrl;
    public string $loginUrlDisplay;

    /**
     * Create a new message instance.
     */
    public function __construct(CompanyOwnerInvitation $invitation, string $token)
    {
        $this->invitation = $invitation;
        $this->token = $token;
        
        $service = app(CompanyOwnerInvitationService::class);
        $this->invitationUrl = $service->getInvitationUrl(
            $invitation->email,
            $token,
            $invitation->subdomain
        );
        $this->loginUrl = $service->getLoginUrl($invitation->subdomain);
        
        // Format login URL display
        $host = request()->getHost();
        if (strpos($host, 'localhost') !== false) {
            $this->loginUrlDisplay = $invitation->subdomain . '.crm.localhost:8000 (Client portal)';
        } else {
            // Use subdomain format (will work once Hostinger configures wildcard subdomain routing)
            $this->loginUrlDisplay = 'http://' . $invitation->subdomain . '.crm.metatech.ae (Client portal)';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re Invited to Join ' . $this->invitation->company_name . ' - Metatech CRM',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.company-owner-invitation',
            with: [
                'invitation' => $this->invitation,
                'invitationUrl' => $this->invitationUrl,
                'loginUrl' => $this->loginUrl,
                'loginUrlDisplay' => $this->loginUrlDisplay,
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
