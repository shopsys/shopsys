<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Country;

use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CountriesQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        protected readonly CountryFacade $countryFacade,
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
