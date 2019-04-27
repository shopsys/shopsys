<?php

declare(strict_types = 1);

namespace Shopsys\ShopBundle\DataFixtures\Translations\Languages;

use Shopsys\ShopBundle\DataFixtures\Demo\AvailabilityDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\CountryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\FlagDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\PaymentDataFixture;
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
        $this->initCategoryTranslations();
        $this->initCountryTranslations();
        $this->initFlagTranslations();
        $this->initPaymentTranslations();
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

    private function initCategoryTranslations(): void
    {
        $translationsCategory = [];

        $translationsCategory[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME] = [
            CategoryDataFixture::CATEGORY_ELECTRONICS => 'Electronics',
            CategoryDataFixture::CATEGORY_TV => 'TV, audio',
            CategoryDataFixture::CATEGORY_PHOTO => 'Cameras & Photo',
            CategoryDataFixture::CATEGORY_PRINTERS => 'Printers',
            CategoryDataFixture::CATEGORY_PC => 'Personal Computers & accessories',
            CategoryDataFixture::CATEGORY_PHONES => 'Mobile Phones',
            CategoryDataFixture::CATEGORY_COFFEE => 'Coffee Machines',
            CategoryDataFixture::CATEGORY_BOOKS => 'Books',
            CategoryDataFixture::CATEGORY_TOYS => 'Toys',
            CategoryDataFixture::CATEGORY_GARDEN_TOOLS => 'Garden tools',
            CategoryDataFixture::CATEGORY_FOOD => 'Food',
        ];

        $translationsCategory[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION] = [
            CategoryDataFixture::CATEGORY_ELECTRONICS => 'Our electronics include devices used for entertainment (flat screen TVs, DVD players, DVD movies, iPods, '
                . 'video games, remote control cars, etc.), communications (telephones, cell phones, e-mail-capable laptops, etc.) '
                . 'and home office activities (e.g., desktop computers, printers, paper shredders, etc.).',
            CategoryDataFixture::CATEGORY_TV => 'Television or TV is a telecommunication medium used for transmitting sound with moving images in monochrome '
                . '(black-and-white), or in color, and in two or three dimensions',
            CategoryDataFixture::CATEGORY_PHOTO => 'A camera is an optical instrument for recording or capturing images, which may be stored locally, '
                . 'transmitted to another location, or both.',
            CategoryDataFixture::CATEGORY_PRINTERS => 'A printer is a peripheral which makes a persistent human readable representation of graphics or text on paper '
                . 'or similar physical media.',
            CategoryDataFixture::CATEGORY_PC => 'A personal computer (PC) is a general-purpose computer whose size, capabilities, and original sale price '
                . 'make it useful for individuals, and is intended to be operated directly by an end-user with no intervening computer '
                . 'time-sharing models that allowed larger, more expensive minicomputer and mainframe systems to be used by many people, '
                . 'usually at the same time.',
            CategoryDataFixture::CATEGORY_PHONES => 'A telephone is a telecommunications device that permits two or more users to conduct a conversation when they are '
                . 'too far apart to be heard directly. A telephone converts sound, typically and most efficiently the human voice, '
                . 'into electronic signals suitable for transmission via cables or other transmission media over long distances, '
                . 'and replays such signals simultaneously in audible form to its user.',
            CategoryDataFixture::CATEGORY_COFFEE => 'Coffeemakers or coffee machines are cooking appliances used to brew coffee. While there are many different types '
                . 'of coffeemakers using a number of different brewing principles, in the most common devices, coffee grounds '
                . 'are placed in a paper or metal filter inside a funnel, which is set over a glass or ceramic coffee pot, '
                . 'a cooking pot in the kettle family. Cold water is poured into a separate chamber, which is then heated up to the '
                . 'boiling point, and directed into the funnel.',
            CategoryDataFixture::CATEGORY_BOOKS => 'A book is a set of written, printed, illustrated, or blank sheets, made of ink, paper, parchment, or other '
                . 'materials, fastened together to hinge at one side. A single sheet within a book is a leaf, and each side of a leaf '
                . 'is a page. A set of text-filled or illustrated pages produced in electronic format is known as an electronic book, '
                . 'or e-book.',
            CategoryDataFixture::CATEGORY_TOYS => 'A toy is an item that can be used for play. Toys are generally played with by children and pets. '
                . 'Playing with toys is an enjoyable means of training young children for life in society. Different materials are '
                . 'used to make toys enjoyable to all ages. ',
            CategoryDataFixture::CATEGORY_GARDEN_TOOLS => 'A garden tool is any one of many tools made for gardens and gardening and overlaps with the range of tools '
                . 'made for agriculture and horticulture. Garden tools can also be hand tools and power tools.',
            CategoryDataFixture::CATEGORY_FOOD => 'Food is any substance consumed to provide nutritional support for the body. It is usually of plant or '
                . 'animal origin, and contains essential nutrients, such as fats, proteins, vitamins, or minerals. The substance '
                . 'is ingested by an organism and assimilated by the organism\'s cells to provide energy, maintain life, '
                . 'or stimulate growth.',
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY] = $translationsCategory;
    }

    private function initCountryTranslations(): void
    {
        $translationsCountry = [
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME => [
                CountryDataFixture::COUNTRY_CZECH_REPUBLIC => 'Czech republic',
                CountryDataFixture::COUNTRY_SLOVAKIA => 'Slovakia',
            ],
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_COUNTRY] = $translationsCountry;
    }

    private function initFlagTranslations(): void
    {
        $translationsFlag = [
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME => [
                FlagDataFixture::FLAG_NEW_PRODUCT => 'New',
                FlagDataFixture::FLAG_TOP_PRODUCT => 'TOP',
                FlagDataFixture::FLAG_ACTION_PRODUCT => 'Action',
            ],
        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_FLAG] = $translationsFlag;
    }

    private function initPaymentTranslations(): void
    {
        $translationsPayment = [];

        $translationsPayment[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME] = [
            PaymentDataFixture::PAYMENT_CARD => 'Credit card',
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY => 'Cash on delivery',
            PaymentDataFixture::PAYMENT_CASH => 'Cash',
        ];

        $translationsPayment[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION] = [
            PaymentDataFixture::PAYMENT_CARD => 'Quick, cheap and reliable!',
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY => '',
            PaymentDataFixture::PAYMENT_CASH => '',
        ];

        $translationsPayment[DataFixturesTranslations::TRANSLATED_ATTRIBUTE_INSTRUCTIONS] = [
            PaymentDataFixture::PAYMENT_CARD => '<b>You have chosen payment by credit card. Please finish it in two business days.</b>',
            PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY => '',
            PaymentDataFixture::PAYMENT_CASH => '',

        ];

        $this->translations[DataFixturesTranslations::TRANSLATED_ENTITY_PAYMENT] = $translationsPayment;
    }
}
