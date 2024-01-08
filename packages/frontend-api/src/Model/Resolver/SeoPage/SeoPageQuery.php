<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\SeoPage;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\SeoPageNotFoundException;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageSlugTransformer;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\SeoPage\Exception\SeoPageNotFoundUserError;

class SeoPageQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade $seoPageFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly SeoPageFacade $seoPageFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param string $pageSlug
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage
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
