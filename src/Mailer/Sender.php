<?php declare(strict_types=1);

namespace Sys\Mailer;

use PHPMailer\PHPMailer\PHPMailer;

class Sender
{
    public PHPMailer $mailer;

    public function __construct()
    {
        $settings = config('mail/mail');

        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $this->mailer->isHTML(true);
        $this->mailer->FromName = $settings['from_name'] ?? '';
        
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

    public function username($username)
    {
        $this->mailer->Username = $username;
    }

    public function password($password)
    {
        $this->mailer->Password = $password;
    }

    public function to(array $to)
    {
        foreach ($to as [$address, $name]) {
            $this->mailer->addAddress($address, $name);
        }
    }

    public function from(array $from)
    {
        $this->mailer->setFrom($from[0], $from[1] ?? '');
        return $this;
    }

    public function subject(string $subject)
    {
        $this->mailer->Subject = $subject;
    }

    public function attach(array $attach)
    {
        foreach ($attach as $path) {
            $this->mailer->addAttachment($path);
        }
    }

    public function body(string $body)
    {
        $this->mailer->Body = $body;
    }

    public function cc(array $cc)
    {
        foreach ($cc as [$address, $name]) {
            $this->mailer->addCC($address, $name);
        }
    }

    public function bcc(array $bcc)
    {
        foreach ($bcc as [$address, $name]) {
            $this->mailer->addBCC($address, $name);
        }
    }

    public function view(string $view, $data = [])
    {
        $this->mailer->Body = view($view, $data);
    }

    public function send(?Email $email = null)
    {
        if ($email) {
            foreach ($email as $key => $value) {
                if ($key === 'view') {
                    $this->view($value, $email->data ?? []);
                } elseif ($key === 'data') {
                    continue;
                } else {
                    $this->$key($value);
                }
            }

            if (empty($this->mailer->From)) {
                $this->mailer->setFrom($this->mailer->Username, $this->mailer->FromName);
            };
        }

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
        $this->mailer->clearReplyTos();

        return $response;
    }
}
