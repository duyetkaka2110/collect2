<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $dataRD;
    public $company;
    public $mailsetting;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->data = $details["data"];
        $this->dataRD = $details["dataRD"];
        $this->company = $details["company"];
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
        return  $mail->cc($this->mailsetting["cc"])->subject('【ガラスリソーシング(株)】依頼申込の確認')
            ->text('request.collect.mail');
    }
}
