<?php declare(strict_types=1);

namespace Sys\Job;

use Az\Session\SessionInterface;

class SessGC
{
    public function __invoke()
    {
        $session = container()->get(SessionInterface::class);
        $count = $session->gc();
        $session->destroy();
        unset($session);
        return $count;
    }
}
