<?php

namespace App\Notifications;

use App\Models\VendorPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The vendor payout instance.
     *
     * @var VendorPayout
     */
    protected $payout;

    /**
     * Create a new notification instance.
     */
    public function __construct(VendorPayout $payout)
    {
        $this->payout = $payout;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Payout Processed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payout has been processed successfully.')
            ->line('Amount: ₹' . number_format($this->payout->amount, 2))
            ->line('Mode: ' . $this->payout->mode);

        if ($this->payout->utr) {
            $message->line('UTR: ' . $this->payout->utr);
        }

        if ($this->payout->status === VendorPayout::STATUS_COMPLETED) {
            $message->line('Status: Completed')
                   ->line('The funds should be reflected in your bank account shortly.');
        } else {
            $message->line('Status: ' . ucfirst($this->payout->status))
                   ->line('You will be notified once the payout is completed.');
        }

        return $message->line('Thank you for your business!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payout_id' => $this->payout->id,
            'amount' => $this->payout->amount,
            'status' => $this->payout->status,
            'mode' => $this->payout->mode,
            'utr' => $this->payout->utr,
            'processed_at' => $this->payout->updated_at->toDateTimeString(),
            'title' => 'Payout Processed',
            'message' => 'Your payout of ₹' . number_format($this->payout->amount, 2) . ' has been processed.',
        ];
    }
}
