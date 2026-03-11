<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefixSettingsDataFactory;
use Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefixSettingsFacade;

class PhonePrefixDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly PhonePrefixSettingsFacade $phonePrefixSettingsFacade,
        private readonly PhonePrefixSettingsDataFactory $phonePrefixSettingsDataFactory,
        private readonly Domain $domain,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $enabledCountryCodes = $this->getEnabledCountriesOnDomain($domainConfig->getId());

            $phonePrefixSettingsData = $this->phonePrefixSettingsDataFactory->create();
            $phonePrefixSettingsData->enabledCodes = $enabledCountryCodes;
            $phonePrefixSettingsData->defaultCode = array_first($enabledCountryCodes);

            $this->phonePrefixSettingsFacade->edit($phonePrefixSettingsData, $domainConfig->getId());
        }
    }

    #[Override]
    public function getDependencies(): array
    {
        return [
            CountryDataFixture::class,
        ];
    }

    /**
     * @return string[]
     */
    private function getEnabledCountriesOnDomain(int $domainId): array
    {
        $countries = [];

        foreach ([CountryDataFixture::COUNTRY_CZECH_REPUBLIC, CountryDataFixture::COUNTRY_SLOVAKIA] as $countryReference) {
            $country = $this->getReference($countryReference, Country::class);

            if ($country->isEnabled($domainId)) {
                $countries[] = $country->getCode();
            }
        }

        return $countries;
    }
}
