<?php

declare(strict_types = 1);

namespace Shopsys\ShopBundle\DataFixtures\Translations\Languages;

use Shopsys\ShopBundle\DataFixtures\Demo\AvailabilityDataFixture;
use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslationInterface;
use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations;

class EnglishTranslations implements DataFixturesTranslationInterface
{
    /**
     * @var array
     */
    private $translations = [];

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return 'en';
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        if (empty($this->translations)) {
            $this->initTranslations();
        }

        return $this->translations;
    }

    private function initTranslations(): void
    {
        $this->initAvailabilityTranslations();
        $this->initBrandTranslations();
    }

    private function initAvailabilityTranslations(): void
    {
        $translationsAvailability = [
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME => [
                AvailabilityDataFixture::AVAILABILITY_IN_STOCK => 'In stock',
                AvailabilityDataFixture::AVAILABILITY_PREPARING => 'Preparing',
                AvailabilityDataFixture::AVAILABILITY_ON_REQUEST => 'On request',
                AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK => 'Out of stock',
            ],
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_AVAILABILITY] = $translationsAvailability;
    }

    private function initBrandTranslations(): void
    {
        $translationsBrand = [
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION => 'This is description of brand %s.',
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_BRAND] = $translationsBrand;
    }
}
