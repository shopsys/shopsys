<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

class CspHeaderSanitizer
{
    public function sanitize(string $cspHeaderValue): string
    {
        $normalizedCspHeaderValue = preg_replace('/\h*\R+\h*/', ' ', $cspHeaderValue);

        return trim($normalizedCspHeaderValue ?? $cspHeaderValue);
    }
}
