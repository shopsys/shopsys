<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class BrandDataFactory implements BrandDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    protected $brandFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        FriendlyUrlFacade $friendlyUrlFacade,
        BrandFacade $brandFacade,
        Domain $domain
    ) {
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->brandFacade = $brandFacade;
        $this->domain = $domain;
    }

    public function create(): BrandData
    {
        $brandData = new BrandData();
        $this->fillNew($brandData);

        return $brandData;
    }

    protected function fillNew(BrandData $brandData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $brandData->seoMetaDescriptions[$domainId] = null;
            $brandData->seoTitles[$domainId] = null;
            $brandData->seoH1s[$domainId] = null;
        }
    }

    public function createFromBrand(Brand $brand): BrandData
    {
        $brandData = new BrandData();
        $this->fillFromBrand($brandData, $brand);

        return $brandData;
    }

    protected function fillFromBrand(BrandData $brandData, Brand $brand): void
    {
        $brandData->name = $brand->getName();

        $translations = $brand->getTranslations();
        /* @var $translations \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation[] */

        $brandData->descriptions = [];
        foreach ($translations as $translation) {
            $brandData->descriptions[$translation->getLocale()] = $translation->getDescription();
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $brandData->seoH1s[$domainId] = $brand->getSeoH1($domainId);
            $brandData->seoTitles[$domainId] = $brand->getSeoTitle($domainId);
            $brandData->seoMetaDescriptions[$domainId] = $brand->getSeoMetaDescription($domainId);

            $brandData->urls->mainFriendlyUrlsByDomainId[$domainId] =
                $this->friendlyUrlFacade->findMainFriendlyUrl(
                    $domainId,
                    'front_brand_detail',
                    $brand->getId()
                );
        }
    }
}
