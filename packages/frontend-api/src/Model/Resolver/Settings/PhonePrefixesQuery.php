<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Country\CountryFlag;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode;
use Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCodeProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Symfony\Component\Intl\Countries;

class PhonePrefixesQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CountryDialCodeProvider $countryDialCodeProvider,
    ) {
    }

    /**
     * @return array<int, array{code: string, dialCode: string, countryName: string, flagEmoji: string}>
     */
    public function phonePrefixesQuery(): array
    {
        $locale = $this->domain->getLocale();

        $countryDialCodes = $this->countryDialCodeProvider->getAllEnabledOnDomainWithDefaultFirst($this->domain->getId());

        return array_map(
            static fn (CountryDialCode $countryDialCode): array => [
                'code' => $countryDialCode->code,
                'dialCode' => $countryDialCode->dialCode,
                'countryName' => Countries::getName($countryDialCode->code, $locale),
                'flagEmoji' => CountryFlag::getFlagEmoji($countryDialCode->code),
            ],
            $countryDialCodes,
        );
    }
}
