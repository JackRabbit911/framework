<?php

namespace Sys\Helper;

enum ResponseType: string
{
    case html = 'html';
    case xml = 'xml';
    case text = 'text';
    case json = 'json';
}
