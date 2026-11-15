<?php

namespace App\Notifications;

use App\Models\BusinessProfile;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessProfileApproved extends Notification
{
    public function __construct(public BusinessProfile $businessProfile) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your business has been approved')
            ->greeting("Hello {$notifiable->name},")
            ->line("Good news! {$this->businessProfile->business_name} has been approved and is now visible in the Osun Marketing Directory.")
            ->action('View your public listing', route('directory.show', $this->businessProfile))
            ->line('Customers can now find your business, see your products and services, and contact you directly.');
    }
}
