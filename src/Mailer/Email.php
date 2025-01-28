<?php declare(strict_types=1);

namespace Sys\Mailer;

use Sys\Mailer\Handler;
use Sys\Model\CommitListener;
use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Exception;

class Email implements IteratorAggregate
{
    public array $data;
    public array $to;
    public array $from;
    public array $cc;
    public array $bcc;
    public array $attach;
    public string $username;
    public string $password;
    public string $subject;
    public string $body;
    public string $view;

    public static function fromJson(string $json)
    {
        $instance = new self;
        $array = json_decode($json, true);

        foreach ($array as $key => $value) {
            $instance->$key = $value;
        }

        return $instance;
    }

    public function toJson()
    {
        return json_encode($this, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this);
    }

    public function __call($key, $args)
    {
        $this->$key = [$args[0], $args[1] ?? []];
        return $this;
    }

    public function to(mixed $to, string $name = ''): self
    {
        if (is_array($to)) {
            foreach ($to as $recipient) {
                if (is_array($recipient)) {
                    $this->address($recipient[0], $recipient[1]);
                    $this->data['username'] = $recipient[1];
                } elseif(is_object($recipient)) {
                    $this->address($recipient->email, $recipient->name);
                    $this->data['username'] = $recipient->name;
                }
            }
        } elseif(is_object($to)) {
            $this->address($to->email, $to->name);
            $this->data['username'] = $to->name;
        } elseif(is_string($to)) {
            $this->address($to, $name);
            $this->data['username'] = $name;
        }

        return $this;
    }

    public function address(string $address, string $name = ''): self
    {
        $this->to[] = [$address, $name];
        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function body(string $body):self
    {
        $this->body = $body;
        return $this;
    }

    public function data(array $data = []): self
    {
        $this->data = $data;
        return $this;
    }

    public function attach(string $path): self
    {
        if (!is_file($path)) {
            throw new Exception(sprintf('File %s not found', $path));
        }

        $this->attach[] = $path;
        return $this;
    }

    public function view(string $view): self
    {
        $this->view = $view;
        return $this;
    }

    public function tpl(string $tpl): self
    {
        $settings = config($tpl);

        if (!$settings) {
            throw new Exception(sprintf('Config %s not found', $tpl));
        }

        foreach ($settings as $key => $value) {
            $value = is_array($value) ? $value : [$value];
            call_user_func_array([$this, $key], $value);
        }

        return $this;
    }

    public function mailbox($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function send(): void
    {
        CommitListener::update($this, Handler::class);
    }
}
