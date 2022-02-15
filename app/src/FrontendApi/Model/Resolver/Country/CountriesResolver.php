<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Country;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;

class CountriesResolver implements ResolverInterface, AliasedInterface
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
    public function resolve(): array
    {
        return $this->countryFacade->getAllEnabledOnCurrentDomain();
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'countries'];
    }
}
