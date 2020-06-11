<?php

declare(strict_types=1);

namespace App\Component\SeoHelper;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
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
     * @param string|null $h1
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     * @return string|null
     */
    public function addH1SeoPagination(?string $h1, PaginationResult $paginationResult): ?string
    {
        if ($h1 === null) {
            return null;
        }

        if ($paginationResult->getPage() > 1 && $paginationResult->getPageCount() > 1) {
            $h1 = $h1 . ' ' . t(
                'strana %currentPage% z %pageCount%',
                [
                    '%currentPage%' => $paginationResult->getPage(),
                    '%pageCount%' => $paginationResult->getPageCount(),
                ]
            );
        }

        return $h1;
    }

    /**
     * @param string|null $title
     * @param \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult $paginationResult
     * @return string|null
     */
    public function addTitleSeoPagination(?string $title, PaginationResult $paginationResult): ?string
    {
        if ($title === null) {
            return null;
        }

        if ($paginationResult->getPage() > 1 && $paginationResult->getPageCount() > 1) {
            $title = $title . ' ' . t(
                'strana %currentPage% z %pageCount%',
                [
                        '%currentPage%' => $paginationResult->getPage(),
                        '%pageCount%' => $paginationResult->getPageCount(),
                    ]
            );
        }

        return $title;
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
