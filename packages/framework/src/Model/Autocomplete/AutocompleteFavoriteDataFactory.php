<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Autocomplete;

class AutocompleteFavoriteDataFactory
{
    public function __construct(
        protected readonly AutocompleteFavoriteFacade $autocompleteFavoriteFacade,
    ) {
    }

    public function createForDomain(int $domainId): AutocompleteFavoriteData
    {
        $autocompleteFavoriteData = $this->createInstance();

        $autocompleteFavoriteData->products = $this->autocompleteFavoriteFacade->getProductsForDomain($domainId);
        $autocompleteFavoriteData->categories = $this->autocompleteFavoriteFacade->getCategoriesForDomain($domainId);
        $autocompleteFavoriteData->brands = $this->autocompleteFavoriteFacade->getBrandsForDomain($domainId);

        return $autocompleteFavoriteData;
    }

    public function createInstance(): AutocompleteFavoriteData
    {
        return new AutocompleteFavoriteData();
    }
}
