<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

class FriendlyUrlSlugNormalizer
{
    public static function normalize(string $slug): string
    {
        $decodedSlug = rawurldecode($slug);
        $decodedSlugSegments = explode('/', $decodedSlug);

        $encodedSlugSegments = array_map(
            static fn (string $segment): string => rawurlencode($segment),
            $decodedSlugSegments,
        );

        return implode('/', $encodedSlugSegments);
    }
}
