<?php

namespace Sys\Cron;

use Cron\CronExpression;
use InvalidArgumentException;

class Expression
{
    private bool $oneOff = false;

    public function nextRunTime(string $expr): int
    {
        if (strpos($expr, '@now') === 0) {
            $this->oneOff = true;
            return time();
        }

        if (($time = $this->secondsParser($expr))) {
            return $time;
        }

        if (($time = strtotime($expr))) {
            $this->oneOff = true;
            return $time;
        }

        if (CronExpression::isValidExpression($expr)) {
            return (new CronExpression($expr))->getNextRunDate()->getTimestamp();
        }

        if (DISPLAY_ERRORS) {
            throw new InvalidArgumentException('Invalid expression');
        } else {
            return false;
        }
    }

    public function isOneOff(string $expr)
    {
        return (strpos($expr, '@now') === 0 || strtotime($expr)) ? true : false;
    }

    private function secondsParser(string $expr)
    {
        if (strpos($expr, '@every') === 0 && strpos($expr, 's') !== false) {
            $is_match = preg_match('/\d+/', $expr, $matches);
            if ($is_match) {
                return time() + (int) $matches[0];
            }
        }

        return false;
    }
}
