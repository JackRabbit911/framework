<?php declare(strict_types=1);

namespace Sys\I18n\Enum;

enum DetectionMethod
{
    case Segment;
    case Subdomain;
    case None;
}
