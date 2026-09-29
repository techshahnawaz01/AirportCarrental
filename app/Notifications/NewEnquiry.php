<?php

namespace App\Notifications;

use App\Models\Enquiry;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEnquiry extends Notification
{
    public function __construct(private readonly Enquiry $enquiry) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New enquiry: '.($this->enquiry->subject ?: $this->enquiry->name))
            ->replyTo($this->enquiry->email, $this->enquiry->name)
            ->greeting('New website enquiry')
            ->line('**From:** '.$this->enquiry->name.' <'.$this->enquiry->email.'>')
            ->line('**Phone:** '.($this->enquiry->phone ?: '—'))
            ->line('**Message:**')
            ->line($this->enquiry->message)
            ->action('Open in admin', route('admin.enquiries.show', $this->enquiry));
    }
}
