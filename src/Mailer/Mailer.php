<?php

namespace Sys\Mailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    public PHPMailer $mailer;

    public function __construct()
    {
        $settings = config('mail/mail');

        $this->mailer = new PHPMailer(true);

        $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $this->mailer->isHTML(true);
        // $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
        
        if($settings['is_smtp'])
        {
            $this->mailer->isSMTP();
            $this->mailer->Host = $settings['smtp'];
            $this->mailer->Port = $settings['smtp_port'];

            if ($settings['smtp_auth']) {
                $this->mailer->SMTPAuth = true;
                $this->mailer->SMTPSecure = $settings['smtp_secure'];
            }
        }
    }

    public function html(bool $is_html)
    {
        $this->mailer->isHTML($is_html);
        return $this;
    }

    public function mailbox($username, $password)
    {
        if ($this->mailer->SMTPAuth === true) {
            $this->mailer->Username = $username;
            $this->mailer->Password = $password;
        }
    }

    public function from($address, $name = '')
    {
        $this->mailer->setFrom($address, $name);
        return $this;
    }

    public function address($address, $name = '')
    {
        $this->mailer->addAddress($address, $name);
        return $this;
    }

    public function subject($subject)
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    public function body($body)
    {
        $this->mailer->Body = $body;
        return $this;
    }

    public function altBody($string = '')
    {
        $this->mailer->AltBody = $string;
        return $this;
    }

    public function cc($address, $name = '')
    {
        $this->mailer->addCC($address, $name);
    }

    public function bcc($address, $name = '')
    {
        $this->mailer->addBCC($address, $name);
    }

    public function send(?Mail $mail = null)
    {
        if(!$this->mailer->send()) {
            $response = [
                'status' => false,
                'message' => $this->mailer->ErrorInfo,
            ];
        } else {
            $response = [
                'status' => true,
                'message' => 'success',
            ];
        }

        $this->mailer->clearAttachments();
        $this->mailer->clearAllRecipients();

        return $response;
    }
}
