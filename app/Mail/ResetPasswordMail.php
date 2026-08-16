<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $nama;
    public $expiredAt;

    public function __construct($resetUrl, $nama, $expiredAt)
    {
        $this->resetUrl = $resetUrl;
        $this->nama = $nama;
        $this->expiredAt = $expiredAt;
    }

    public function build()
    {
        return $this->subject('Reset Password - SIMMAGI')
                    ->view('emails.reset_password');
    }
}