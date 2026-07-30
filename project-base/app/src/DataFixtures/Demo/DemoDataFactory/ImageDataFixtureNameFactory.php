<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\DemoDataFactory;

use App\Model\Product\Product;
use LogicException;
use Shopsys\FrameworkBundle\Component\DataFixture\DomainsForDataFixtureProvider;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

final class ImageDataFixtureNameFactory
{
    public function __construct(
        private readonly DomainsForDataFixtureProvider $domainsForDataFixtureProvider,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function createProductImageNames(
        Product $product,
        bool $isRemoteControlImage,
        int $position,
    ): array {
        $names = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $translationParameters = ['%productName%' => $product->getFullName($locale)];
            $names[$locale] = match (true) {
                $isRemoteControlImage => t(
                    'Remote control of %productName%',
                    $translationParameters,
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                $position === 0 => t(
                    'Front view of %productName%',
                    $translationParameters,
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                default => t(
                    'Alternative view of %productName%',
                    $translationParameters,
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
            };
        }

        return $names;
    }

    /**
     * @return array<string, string>
     */
    public function createSliderItemImageNames(int $sliderItemId): array
    {
        $names = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $names[$locale] = match (($sliderItemId - 1) % 3) {
                0 => t(
                    'E-commerce order preparation in a modern warehouse',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                1 => t(
                    'Developer working with Shopsys Platform documentation',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                2 => t(
                    'Shopsys team collaborating in an office',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
                default => throw new LogicException(sprintf('Unexpected slider item ID: %d', $sliderItemId)),
            };
        }

        return $names;
    }
}
