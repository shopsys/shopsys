<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Country;

use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CountriesQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        private readonly CountryFacade $countryFacade
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function countriesQuery(): array
    {
        return $this->countryFacade->getAllEnabledOnCurrentDomain();
    }
}
