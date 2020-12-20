<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DojoSubscriptionUpdated extends Notification
{
    use Queueable;

    private $dojo;
    public $plan;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($dojo,$plan)
    {
        $this->dojo = $dojo;
        $this->plan = $plan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if ($this->plan->stripe_id == "free_plan") {
            $description = "free";
        } else {
            $description = $this->plan->description;
        }
        
        return (new MailMessage)
                    ->line("Your subscription for the dojo ".$this->dojo->name." was updated to the ".$description." plan!")
                    ->line("Thank you for using ".config("app.name")."!")
                    ->action('View your dojos!', url('/'));
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
