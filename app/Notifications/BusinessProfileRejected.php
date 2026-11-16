<?php

namespace App\Notifications;

use App\Models\BusinessProfile;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessProfileRejected extends Notification
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
            ->subject('Your business profile needs changes')
            ->greeting("Hello {$notifiable->name},")
            ->line("An administrator reviewed {$this->businessProfile->business_name} and could not approve it yet.")
            ->line("Reason: {$this->businessProfile->rejection_reason}")
            ->action('Update your business profile', route('business-profile.edit'))
            ->line('After you update your details, submit the profile again for review.');
    }
}
