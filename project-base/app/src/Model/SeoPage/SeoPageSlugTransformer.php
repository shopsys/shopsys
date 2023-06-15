<?php

declare(strict_types=1);

namespace App\Model\SeoPage;

class SeoPageSlugTransformer
{
    /**
     * @param string $friendlyUrl
     * @return string
     */
    public static function transformFriendlyUrlToSeoPageSlug(string $friendlyUrl): string
    {
        $pageSlug = ltrim($friendlyUrl, '/');

        return $pageSlug === ''
            ? SeoPage::SEO_PAGE_HOMEPAGE_SLUG
            : $pageSlug;
    }
}
