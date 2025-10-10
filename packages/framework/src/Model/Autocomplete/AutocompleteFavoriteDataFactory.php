<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

class AutocompleteFavoriteDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteFacade $autocompleteFavoriteFacade
     */
    public function __construct(
        protected readonly AutocompleteFavoriteFacade $autocompleteFavoriteFacade,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteData
     */
    public function createForDomain(int $domainId): AutocompleteFavoriteData
    {
        $autocompleteFavoriteData = $this->createInstance();

        $autocompleteFavoriteData->products = $this->autocompleteFavoriteFacade->getProductsForDomain($domainId);
        $autocompleteFavoriteData->categories = $this->autocompleteFavoriteFacade->getCategoriesForDomain($domainId);
        $autocompleteFavoriteData->brands = $this->autocompleteFavoriteFacade->getBrandsForDomain($domainId);

        return $autocompleteFavoriteData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteData
     */
    public function createInstance(): AutocompleteFavoriteData
    {
        return new AutocompleteFavoriteData();
    }
}
