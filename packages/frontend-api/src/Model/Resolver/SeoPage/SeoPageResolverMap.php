<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\SeoPage;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;

class SeoPageResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'SeoPage' => [
                'seo' => fn (SeoPage $seoPage) => $seoPage->getSeoAttributes($this->domain->getId()),
                'ogTitle' => fn (SeoPage $seoPage) => $seoPage->getSeoOgTitle($this->domain->getId()),
                'ogDescription' => fn (SeoPage $seoPage) => $seoPage->getSeoOgDescription($this->domain->getId()),
                'hreflangLinks' => fn (SeoPage $seoPage) => $this->hreflangLinksFacade->getForSeoPage($seoPage, $this->domain->getId()),
            ],
        ];
    }
}
