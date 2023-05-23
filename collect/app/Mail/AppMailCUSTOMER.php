<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppMailCUSTOMER extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $dataRD;
    public $company;
    public $mailsetting;
    public $type;
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
        $this->type = $details["type"];
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
        
        if ($this->mailsetting["cc"]) {
            $mail = $mail->cc($this->mailsetting["cc"]);
        }
        // 【配車】ボタン押下時
        if ($this->type == "button") {
            return  $mail->subject('【ガラスリソーシング(株)】回収日の確認')
                ->text('admin.collect.request.mail_conf');
        }
        //「回収日」変更時
        if ($this->type == "change") {
            return  $mail->subject('【ガラスリソーシング(株)】回収日の変更')
                ->text('admin.collect.request.mail_chg');
        }
    }
}
