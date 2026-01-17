<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\String;

class DatabaseSearchingHelper
{
    public static function getLikeSearchString(string $string): string
    {
        // LIKE pattern must not end with escape character in Postgres
        $string = rtrim($string, '\\');
        $string = str_replace(
            ['%', '_', '*', '?'],
            ['\%', '\_', '%', '_'],
            $string,
        );

        return $string;
    }

    public function getFullTextLikeSearchString(string $string): string
    {
        return '%' . self::getLikeSearchString($string) . '%';
    }
}
