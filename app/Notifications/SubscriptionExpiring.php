<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiring extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The vendor instance.
     *
     * @var Vendor
     */
    protected $vendor;

    /**
     * The subscription instance.
     *
     * @var UserSubscription
     */
    protected $subscription;

    /**
     * Days until expiry.
     *
     * @var int
     */
    protected $daysRemaining;

    /**
     * Create a new notification instance.
     */
    public function __construct(Vendor $vendor, UserSubscription $subscription, int $daysRemaining)
    {
        $this->vendor = $vendor;
        $this->subscription = $subscription;
        $this->daysRemaining = $daysRemaining;
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
        $planName = $this->subscription->plan ? $this->subscription->plan->name : 'Your Plan';
        $expiryDate = $this->subscription->ends_at->format('F d, Y');
        
        $message = (new MailMessage)
            ->subject('⚠️ Subscription Expiring Soon - ' . $this->vendor->store_name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a friendly reminder that your subscription is expiring soon.');

        if ($this->daysRemaining > 1) {
            $message->line('**Your subscription will expire in ' . $this->daysRemaining . ' days.**');
        } else {
            $message->line('**Your subscription will expire tomorrow!**');
        }

        $message->line('**Store:** ' . $this->vendor->store_name)
                ->line('**Plan:** ' . $planName)
                ->line('**Expiry Date:** ' . $expiryDate)
                ->line('To continue enjoying uninterrupted service, please renew your subscription before it expires.')
                ->action('Renew Subscription', url('/vendor/subscription/plans'))
                ->line('If you have any questions or need assistance, please contact our support team.')
                ->line('Thank you for being a valued vendor!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $planName = $this->subscription->plan ? $this->subscription->plan->name : 'Your Plan';
        
        return [
            'type' => 'subscription_expiring',
            'vendor_id' => $this->vendor->id,
            'subscription_id' => $this->subscription->id,
            'days_remaining' => $this->daysRemaining,
            'expiry_date' => $this->subscription->ends_at->toDateTimeString(),
            'plan_name' => $planName,
            'title' => 'Subscription Expiring Soon',
            'message' => 'Your subscription for ' . $this->vendor->store_name . ' will expire in ' . $this->daysRemaining . ' day' . ($this->daysRemaining > 1 ? 's' : '') . '.',
            'action_url' => url('/vendor/subscription/plans'),
        ];
    }
}
