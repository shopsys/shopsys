<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\SeoPage;

use App\FrontendApi\Resolver\SeoPage\Exception\SeoPageNotFoundUserError;
use App\Model\SeoPage\Exception\SeoPageNotFoundException;
use App\Model\SeoPage\SeoPage;
use App\Model\SeoPage\SeoPageFacade;
use App\Model\SeoPage\SeoPageSlugTransformer;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class SeoPageQuery extends AbstractQuery
{
    /**
     * @param \App\Model\SeoPage\SeoPageFacade $seoPageFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly SeoPageFacade $seoPageFacade,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param string $pageSlug
     * @return \App\Model\SeoPage\SeoPage
     */
    public function seoPageByPageSlugQuery(string $pageSlug): SeoPage
    {
        $domainId = $this->domain->getId();

        try {
            $slug = SeoPageSlugTransformer::transformFriendlyUrlToSeoPageSlug($pageSlug);
            $seoPage = $this->seoPageFacade->getByDomainIdAndPageSlug($domainId, $slug);
        } catch (SeoPageNotFoundException $seoPageNotFoundException) {
            throw new SeoPageNotFoundUserError($seoPageNotFoundException->getMessage());
        }

        return $seoPage;
    }
}
