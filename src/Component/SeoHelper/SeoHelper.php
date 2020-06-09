<?php

declare(strict_types=1);

namespace App\Component\SeoHelper;

use Symfony\Component\HttpFoundation\Request;

class SeoHelper
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $page
     * @return bool
     */
    public function disableIndexingBySeznamBot(Request $request, int $page): bool
    {
        return  $page > 1 && $this->isUserAgentSeznamBot($request);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    private function isUserAgentSeznamBot(Request $request): bool
    {
        $userAgent = $request->headers->get('User-Agent');

        return  $userAgent !== null && preg_match('/SeznamBot/i', $userAgent);
    }
}
