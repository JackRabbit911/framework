<?php declare(strict_types=1);

namespace Sys\Mailer;

class Handler
{
    private Sender $mailer;

    public function __construct(Sender $mailer)
    {
        $this->mailer = $mailer;
    }

    public function save(Email $entity)
    {
        return $this->send($entity);
    }

    public function send(Email $entity)
    {
        return $this->mailer->send($entity);
    }
}
