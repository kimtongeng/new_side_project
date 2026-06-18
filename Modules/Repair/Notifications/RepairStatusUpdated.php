<?php

namespace Modules\Repair\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RepairStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $notification_data;

    public function __construct($notification_data)
    {
        $this->notification_data = $notification_data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database',
        // 'mail'
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //         ->subject($this->notification_data['subject'])
    //         ->view(
    //             'emails.plain_html',
    //             ['content' => $this->notification_data['body']]
    //         );
    // }
    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->notification_data['subject'],
            'body' => $this->notification_data['body'],
            'repair_id' => $this->notification_data['repair_id'] ?? null,
        ];
    }
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
