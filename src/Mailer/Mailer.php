<?php

namespace Sys\Mailer;

use Error;
use PHPMailer\PHPMailer\PHPMailer;
use Sys\Trait\Options;

class Mailer
{
    use Options;

    public PHPMailer $mailer;
    private $default_charset = 'utf-8';
    private $is_smtp = true;
    private $is_imap = false;
    private $smtp;
    private $smtp_port;
    private $smtp_auth = true;
    private $smtp_secure = 'tls';
    // private $pop3;
    // private $pop3_box;
    // private $imap;
    // private $imapbox;
    private $mailboxes = [];
    private $is_html = true;

    public function __construct()
    {
        $settings = config('mail/mail');
        $this->is_smtp = $settings['is_smtp'];
        $debug = (IS_DEBUG) ? true : null;
        $debug = true;

        $this->mailer = new PHPMailer($debug);
        $this->mailer->CharSet = $this->default_charset;

        if($this->is_smtp)
        {
            $this->mailer->isSMTP();
            $this->mailer->Host = $settings['smtp'];
            $this->mailer->Port = $settings['smtp_port'];

            if ($settings['smtp_auth']) {
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = $settings['from_address'];
                $this->mailer->Password = $settings['password'];
                $this->mailer->SMTPSecure = $this->smtp_secure;
            }            
        }

        $this->mailer->isHTML($this->is_html);
        $this->mailer->setFrom($settings['from_address'], $settings['from_name']);
    }

    public function send(Mail $mail)
    {
        $this->mailer->clearAttachments();
        $this->mailer->clearAllRecipients();

        $mail->setData();
        $mail->render();

        foreach ($mail as $key => $value) {
            if (property_exists($this->mailer, $key)) {
                $this->mailer->$key = (is_array($value)) ? $value[0] : $value;
            } elseif (method_exists($this->mailer, $key)) {
                if ($key == 'addAddress') {
                    foreach ($value as $val) {
                        $this->mailer->addAddress($val[0], $val[1] ?? '');
                    }
                } else {
                    call_user_func_array([$this->mailer, $key], $value);
                }
            } else {
                $ukey = ucfirst($key);
                if (property_exists($this->mailer, $ukey)) {
                    $this->mailer->$ukey = (is_array($value)) ? $value[0] : $value;
                } elseif (method_exists($this->mailer, $ukey)) {
                    call_user_func_array([$this->mailer, $ukey], $value);
                } else {
                    throw new Error("Call to undefined method PHPMailer::$key");
                }
            }
        }
        
        if(!$this->mailer->send()) {
            $response = [
                'status' => false,
                'message' => $this->mailer->ErrorInfo,
                'context' => $mail->addAddress,
            ];
        } else {
            $response = [
                'status' => true,
                'message' => 'success',
            ];
        }

        return $response;
    }
}
