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

class VendorRejected extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Vendor $vendor;
    public ?string $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Vendor $vendor, ?string $rejectionReason = null)
    {
        $this->user = $user;
        $this->vendor = $vendor;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on Your Vendor Application - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor.rejected',
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
