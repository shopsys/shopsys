<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Brand;

use App\FrontendApi\Model\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade as BaseBrandFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\BrandsQuery as BaseBrandsQuery;

class BrandsQuery extends BaseBrandsQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Brand\BrandFacade $apiBrandFacade
     */
    public function __construct(
        BaseBrandFacade $brandFacade,
        Domain $domain,
        private readonly BrandFacade $apiBrandFacade,
    ) {
        parent::__construct($brandFacade, $domain);
    }

    /**
     * @return \App\Model\Product\Brand\Brand[]
     */
    public function brandsQuery(): array
    {
        return $this->apiBrandFacade->getAllWithDomainsAndTranslations($this->domain->getCurrentDomainConfig());
    }
}
