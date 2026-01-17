<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

class SeoPageSlugTransformer
{
    public function transformFriendlyUrlToSeoPageSlug(string $friendlyUrl): string
    {
        $pageSlug = ltrim($friendlyUrl, '/');

        return $pageSlug === ''
            ? SeoPage::SEO_PAGE_HOMEPAGE_SLUG
            : $pageSlug;
    }
}
