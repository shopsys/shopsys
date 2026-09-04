<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\String;

use Transliterator;

class TransformStringHelper
{
    public function safeFilename(string $string): string
    {
        $string = preg_replace('~[^-\\.\\pL0-9_]+~u', '_', $string);
        $string = preg_replace('~[\\.]{2,}~u', '.', $string);
        $string = trim($string, '_');
        $string = $this->toAscii($string);
        $string = preg_replace('~[^-\\.a-zA-Z0-9_]+~', '', $string);
        $string = ltrim($string, '.');

        return $string;
    }

    public static function emptyToNull(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    public static function getTrimmedStringOrNullOnEmpty(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return self::emptyToNull(trim($value));
    }

    public function stringToFriendlyUrlSlug(string $string): string
    {
        return static::createFriendlyUrlSlug($string);
    }

    /**
     * @see http://php.vrana.cz/vytvoreni-pratelskeho-url.php
     */
    public static function createFriendlyUrlSlug(string $string): string
    {
        $slug = preg_replace('~[^\\pL0-9_]+~u', '-', $string);
        $slug = trim($slug, '-');
        $slug = static::toAscii($slug);
        $slug = strtolower($slug);

        return preg_replace('~[^-a-z0-9_]+~', '', $slug);
    }

    public function addOrRemoveTrailingSlashFromString(string $string): string
    {
        if (str_ends_with($string, '/')) {
            return rtrim($string, '/');
        }

        return $string . '/';
    }

    /**
     * Transforms arbitrary string (natural sentence, under_score, PascalCase, ...) into one ascii camelCase string
     *
     * @see \Tests\FrameworkBundle\Unit\Component\String\TransformStringTest::stringToCamelCaseProvider() for example usages
     */
    public function stringToCamelCase(string $string): string
    {
        // convert everything apart from letters and numbers into spaces
        $string = preg_replace('~[^\\pL0-9]+~u', ' ', $string);
        // transliterate into ascii
        $string = $this->toAscii($string);
        // remove special characters after transliteration
        $string = preg_replace('~[^a-zA-Z0-9 ]~', '', $string);
        // preserve camel case by splitting words with spaces
        $string = preg_replace('~([a-z])([A-Z])~', '$1 $2', $string);
        // capitalize only first letter of every word
        $string = ucwords(strtolower($string), ' ');
        // squash words
        $string = str_replace(' ', '', $string);
        // lowercase first letter
        $string = lcfirst($string);

        return $string;
    }

    protected static function toAscii(string $string): string
    {
        $transliteratorToLatin = Transliterator::create('Any-Latin');
        $transliteratorToAscii = Transliterator::create('Latin-ASCII');

        return $string
            |> $transliteratorToLatin->transliterate(...)
            |> $transliteratorToAscii->transliterate(...);
    }

    /**
     * For some cases there is need to have clean absolute paths
     * Operating systems that use drive letter assignments https://en.wikipedia.org/wiki/Drive_letter_assignment
     */
    public function removeDriveLetterFromPath(string $path): string
    {
        return preg_replace('#^[A-Z]:#', '', $path);
    }

    public function replaceOccurrences(string $search, string $replace, string $subject, int $limit = 1): string
    {
        $search = '/' . preg_quote($search, '/') . '/';

        return preg_replace($search, $replace, $subject, $limit);
    }

    public static function convertHtmlToPlainText(?string $htmlText): ?string
    {
        if ($htmlText === null) {
            return null;
        }

        return $htmlText
            |> html_entity_decode(...)
            |> (fn ($v) => str_replace('<', ' <', $v))
            |> strip_tags(...)
            |> (fn ($v) => preg_replace('/\s\s+/', ' ', $v))
            |> trim(...);
    }

    public function removeStringFromStart(string $string, string $stringToRemove): string
    {
        if (str_starts_with($string, $stringToRemove)) {
            return substr($string, strlen($stringToRemove));
        }

        return $string;
    }

    public function removeStringFromEnd(string $string, string $stringToRemove): string
    {
        if (str_ends_with($string, $stringToRemove)) {
            return substr($string, 0, -strlen($stringToRemove));
        }

        return $string;
    }
}
