<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppMailCarryFinalError extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $mailsetting;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->data = $details["data"];
        $this->mailsetting = $details["mailsetting"];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->from($this->mailsetting["from"], $this->mailsetting["fromname"]);
        if ($this->mailsetting["to"]) {
            $mail = $mail->to($this->mailsetting["to"]);
        }
        return  $mail->subject('【引取管理システム】回収完了データ取込エラー')
            ->text('admin.carry.mail_getcomp_err');
    }
}
