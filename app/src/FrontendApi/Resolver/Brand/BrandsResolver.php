<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Brand;

use App\FrontendApi\Model\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade as BaseBrandFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandsResolver as BaseBrandsResolver;

/**
 * @property \App\Model\Product\Brand\BrandFacade $brandFacade
 */
class BrandsResolver extends BaseBrandsResolver
{
    /**
     * @var \App\FrontendApi\Model\Brand\BrandFacade
     */
    private BrandFacade $apiBrandFacade;

    /**
     * @param \App\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Brand\BrandFacade $apiBrandFacade
     */
    public function __construct(BaseBrandFacade $brandFacade, Domain $domain, BrandFacade $apiBrandFacade)
    {
        parent::__construct($brandFacade, $domain);

        $this->apiBrandFacade = $apiBrandFacade;
    }

    /**
     * @return \App\Model\Product\Brand\Brand[]
     */
    public function resolve(): array
    {
        return $this->apiBrandFacade->getAllWithDomainsAndTranslations($this->domain->getCurrentDomainConfig());
    }
}
