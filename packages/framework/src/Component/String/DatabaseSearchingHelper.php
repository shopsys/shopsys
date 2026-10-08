<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\String;

class DatabaseSearchingHelper
{
    public static function getLikeSearchString(string $string): string
    {
        return str_replace(
            ['\\', '%', '_', '*', '?'],
            ['\\\\', '\%', '\_', '%', '_'],
            $string,
        );
    }

    public function getFullTextLikeSearchString(string $string): string
    {
        return '%' . self::getLikeSearchString($string) . '%';
    }
}
