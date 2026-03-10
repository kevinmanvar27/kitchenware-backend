<?php

namespace App\Mail;

use App\Models\Vendor;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorSuspended extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Vendor $vendor;
    public ?string $suspensionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Vendor $vendor, ?string $suspensionReason = null)
    {
        $this->user = $user;
        $this->vendor = $vendor;
        $this->suspensionReason = $suspensionReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Important: Your Vendor Account Has Been Suspended - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor.suspended',
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
