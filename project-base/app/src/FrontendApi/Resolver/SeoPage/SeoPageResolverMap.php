<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\SeoPage;

use App\Model\SeoPage\SeoPage;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class SeoPageResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly Domain $domain,
    ) {
    }

    /**
     * @return array<'SeoPage', array<'canonicalUrl'|'metaDescription'|'ogDescription'|'ogTitle'|'title', \Closure(App\Model\SeoPage\SeoPage $seoPage): (string | null)>>
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
            ],
        ];
    }
}
