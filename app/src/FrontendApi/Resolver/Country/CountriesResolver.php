<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Country;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

class CountriesResolver implements QueryInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade
     */
    private CountryFacade $countryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        CountryFacade $countryFacade
    ) {
        $this->countryFacade = $countryFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country[]
     */
    public function resolveCountries(): array
    {
        return $this->countryFacade->getAllEnabledOnCurrentDomain();
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveCountries' => 'resolveCountries'];
    }
}
