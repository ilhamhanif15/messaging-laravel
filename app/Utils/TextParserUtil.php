<?php

namespace App\Utils;

class TextParserUtil
{
    public static function convertTrailingBreaks(string $text): string
    {
        $breaks = array("</p><p>");
        return str_ireplace($breaks, "<br/>", $text);
    }
}