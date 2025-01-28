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
    public array $address;
    public array $from;
    public array $cc;
    public array $bcc;
    public array $attach;
    public array $reply;
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

    public function address(string $address, string $name = ''): self
    {
        $this->address[] = [$address, $name];
        return $this;
    }

    public function cc(string $address, string $name = ''): self
    {
        $this->cc[] = [$address, $name];
        return $this;
    }

    public function bcc(string $address, string $name = ''): self
    {
        $this->bcc[] = [$address, $name];
        return $this;
    }
    
    public function reply(string $address, string $name = ''): self
    {
        $this->reply[] = [$address, $name];
        return $this;
    }

    public function from(string $address, string $name = ''): self
    {
        $this->from = [$address, $name];
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
   
    public function attach(string $path): self
    {
        if (!is_file($path)) {
            throw new Exception(sprintf('File %s not found', $path));
        }
        
        $this->attach[] = $path;
        return $this;
    }


    public function username(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function password(string $password): self
    {
        $this->username = $password;
        return $this;
    }

    public function data(array $data = []): self
    {
        $this->data = $data;
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

    public function send(): void
    {
        CommitListener::update($this, Handler::class);
    }
}
