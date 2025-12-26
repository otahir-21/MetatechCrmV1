<?php

namespace App\Mail;

use App\Models\StaffInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public StaffInvitation $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(StaffInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Staff Invitation - ' . $this->invitation->company->company_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Build URL based on company subdomain
        $subdomain = $this->invitation->company->subdomain;
        $scheme = request()->getScheme();
        $host = request()->getHost();
        $port = request()->getPort();
        
        // For production, use the company subdomain
        if (strpos($host, 'metatech.ae') !== false) {
            $baseUrl = $scheme . '://' . $subdomain . '.crm.metatech.ae';
        } else {
            // For localhost development
            $portSuffix = ($port && $port !== 80 && $port !== 443) ? ':' . $port : ':8000';
            $baseUrl = $scheme . '://' . $subdomain . '.localhost' . $portSuffix;
        }
        
        $acceptUrl = $baseUrl . '/accept-invitation/' . $this->invitation->token;
        
        return new Content(
            view: 'emails.staff-invitation',
            with: [
                'invitation' => $this->invitation,
                'acceptUrl' => $acceptUrl,
                'companyName' => $this->invitation->company->company_name,
                'inviterName' => $this->invitation->invitedBy->name ?? $this->invitation->invitedBy->email,
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
