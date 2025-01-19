<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendGridMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $message;

    /**
     * Create a new message instance.
     *
     * @param string $subject
     * @param string $message
     * @return void
     */
    public function __construct($subject, $message)
    {
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return \Illuminate\Contracts\Mail\Mailable
     */
    public function build()
    {
        // Build the email using SendGrid's setup
        return $this->view('emails.sendgrid')  // View where email content is built (see below)
                    ->with([
                        'message' => $this->message,
                    ])
                    ->subject($this->subject);  // Set the subject
    }
}
