<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpired extends Notification implements ShouldQueue
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
     * Create a new notification instance.
     */
    public function __construct(Vendor $vendor, UserSubscription $subscription)
    {
        $this->vendor = $vendor;
        $this->subscription = $subscription;
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
        
        return (new MailMessage)
            ->subject('🚨 Subscription Expired - ' . $this->vendor->store_name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your subscription has expired.')
            ->line('**Store:** ' . $this->vendor->store_name)
            ->line('**Plan:** ' . $planName)
            ->line('**Expired On:** ' . $expiryDate)
            ->line('Your store may have limited functionality until you renew your subscription.')
            ->line('To restore full access to all features, please renew your subscription now.')
            ->action('Renew Subscription', url('/vendor/subscription/plans'))
            ->line('If you have any questions or need assistance, please contact our support team.')
            ->line('We look forward to continuing our partnership with you!');
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
            'type' => 'subscription_expired',
            'vendor_id' => $this->vendor->id,
            'subscription_id' => $this->subscription->id,
            'expiry_date' => $this->subscription->ends_at->toDateTimeString(),
            'plan_name' => $planName,
            'title' => 'Subscription Expired',
            'message' => 'Your subscription for ' . $this->vendor->store_name . ' has expired. Please renew to continue using all features.',
            'action_url' => url('/vendor/subscription/plans'),
        ];
    }
}
