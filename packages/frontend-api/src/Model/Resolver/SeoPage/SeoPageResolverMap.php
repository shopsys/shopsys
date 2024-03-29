<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\SeoPage;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;

class SeoPageResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'SeoPage' => [
                'title' => fn (SeoPage $seoPage) => $seoPage->getSeoTitle($this->domain->getId()),
                'metaDescription' => fn (SeoPage $seoPage) => $seoPage->getSeoMetaDescription($this->domain->getId()),
                'canonicalUrl' => fn (SeoPage $seoPage) => $seoPage->getCanonicalUrl($this->domain->getId()),
                'ogTitle' => fn (SeoPage $seoPage) => $seoPage->getSeoOgTitle($this->domain->getId()),
                'ogDescription' => fn (SeoPage $seoPage) => $seoPage->getSeoOgDescription($this->domain->getId()),
                'hreflangLinks' => fn (SeoPage $seoPage) => $this->hreflangLinksFacade->getForSeoPage($seoPage, $this->domain->getId()),
            ],
        ];
    }
}
