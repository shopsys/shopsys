<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
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
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null $imageFacade
     */
    public function __construct(
        FriendlyUrlFacade $friendlyUrlFacade,
        BrandFacade $brandFacade,
        Domain $domain,
        ?ImageFacade $imageFacade = null
    ) {
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->brandFacade = $brandFacade;
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function setImageFacade(ImageFacade $imageFacade): void
    {
        if ($this->imageFacade !== null && $this->imageFacade !== $imageFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->imageFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->imageFacade = $imageFacade;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData
     */
    public function create(): BrandData
    {
        $brandData = new BrandData();
        $this->fillNew($brandData);

        return $brandData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData $brandData
     */
    protected function fillNew(BrandData $brandData)
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $brandData->seoMetaDescriptions[$domainId] = null;
            $brandData->seoTitles[$domainId] = null;
            $brandData->seoH1s[$domainId] = null;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $brandData->descriptions[$locale] = null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData
     */
    public function createFromBrand(Brand $brand): BrandData
    {
        $brandData = new BrandData();
        $this->fillFromBrand($brandData, $brand);

        return $brandData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData $brandData
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     */
    protected function fillFromBrand(BrandData $brandData, Brand $brand)
    {
        $brandData->name = $brand->getName();

        /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation[] $translations */
        $translations = $brand->getTranslations();

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

        $brandData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($brand, null);
    }
}
