<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\Category\CategoryDataFactory;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\NewProductsCategoryAutomatedFilter;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\OnStockCategoryAutomatedFilter;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class CategoryDataFixture extends AbstractReferenceFixture
{
    private const string UUID_NAMESPACE = '7ba89a28-77ab-419a-b154-ef747d7a98ce';

    public const string CATEGORY_ELECTRONICS = 'category_electronics';
    public const string CATEGORY_TV = 'category_tv';
    public const string CATEGORY_PHOTO = 'category_photo';
    public const string CATEGORY_PRINTERS = 'category_printers';
    public const string CATEGORY_PC = 'category_pc';
    public const string CATEGORY_PHONES = 'category_phones';
    public const string CATEGORY_COFFEE = 'category_coffee';
    public const string CATEGORY_BOOKS = 'category_books';
    public const string CATEGORY_TOYS = 'category_toys';
    public const string CATEGORY_GARDEN_TOOLS = 'category_garden_tools';
    public const string CATEGORY_FOOD = 'category_food';
    public const string CATEGORY_LAPTOPS = 'category_laptops';
    public const string CATEGORY_DESKTOP_COMPUTERS = 'category_desktop_computers';
    public const string CATEGORY_COMPUTER_ACCESSORIES = 'category_computer_accessories';
    public const string CATEGORY_TELEVISIONS = 'category_televisions';
    public const string CATEGORY_HEADPHONES = 'category_headphones';
    public const string CATEGORY_HOME_CINEMA_SYSTEMS = 'category_home_cinema_systems';
    public const string CATEGORY_DIGITAL_CAMERAS = 'category_digital_cameras';
    public const string CATEGORY_CAMERA_LENSES = 'category_camera_lenses';
    public const string CATEGORY_CAMERA_ACCESSORIES = 'category_camera_accessories';
    public const string CATEGORY_INKJET_PRINTERS = 'category_inkjet_printers';
    public const string CATEGORY_LASER_PRINTERS = 'category_laser_printers';
    public const string CATEGORY_PRINTER_SUPPLIES = 'category_printer_supplies';
    public const string CATEGORY_SMARTPHONES = 'category_smartphones';
    public const string CATEGORY_MOBILE_PHONE_ACCESSORIES = 'category_mobile_phone_accessories';
    public const string CATEGORY_SMARTWATCHES = 'category_smartwatches';
    public const string CATEGORY_AUTOMATIC_COFFEE_MACHINES = 'category_automatic_coffee_machines';
    public const string CATEGORY_CAPSULE_COFFEE_MACHINES = 'category_capsule_coffee_machines';
    public const string CATEGORY_COFFEE_GRINDERS = 'category_coffee_grinders';
    public const string CATEGORY_FICTION = 'category_fiction';
    public const string CATEGORY_NON_FICTION = 'category_non_fiction';
    public const string CATEGORY_CHILDRENS_BOOKS = 'category_childrens_books';
    public const string CATEGORY_BUILDING_SETS = 'category_building_sets';
    public const string CATEGORY_BOARD_GAMES = 'category_board_games';
    public const string CATEGORY_OUTDOOR_TOYS = 'category_outdoor_toys';
    public const string CATEGORY_HAND_TOOLS = 'category_hand_tools';
    public const string CATEGORY_POWER_TOOLS = 'category_power_tools';
    public const string CATEGORY_WATERING_SYSTEMS = 'category_watering_systems';
    public const string CATEGORY_SNACKS = 'category_snacks';
    public const string CATEGORY_COFFEE_AND_TEA = 'category_coffee_and_tea';
    public const string CATEGORY_PANTRY_STAPLES = 'category_pantry_staples';

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        private readonly CategoryFacade $categoryFacade,
        private readonly CategoryDataFactory $categoryDataFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        /**
         * Root category is created in database migration.
         *
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135345
         * @var \App\Model\Category\Category $rootCategory
         */
        $rootCategory = $this->categoryFacade->getRootCategory();
        $categoryData = $this->categoryDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'Our electronics include devices used for entertainment (flat screen TVs, DVD players, DVD movies, iPods, '
                    . 'video games, remote control cars, etc.), communications (telephones, cell phones, email-capable laptops, etc.) '
                    . 'and home office activities (e.g., desktop computers, printers, paper shredders, etc.).',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );

            $categoryData->seoH1s[$domainConfig->getId()] = t(
                'Electronic devices',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
            $categoryData->seoTitles[$domainConfig->getId()] = t(
                'Electronic stuff',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
            $categoryData->seoMetaDescriptions[$domainConfig->getId()] = t(
                'All kind of electronic devices.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $categoryData->parent = $rootCategory;
        $this->createCategory($categoryData, self::CATEGORY_ELECTRONICS);

        $categoryData = $this->categoryDataFactory->create();

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'Television or TV is a telecommunication medium used for transmitting sound with moving images in monochrome '
                    . '(black-and-white), or in color, and in two or three dimensions',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $categoryElectronics = $this->getReference(self::CATEGORY_ELECTRONICS, Category::class);
        $categoryData->parent = $categoryElectronics;
        $this->createCategory($categoryData, self::CATEGORY_TV);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'A camera is an optical instrument for recording or capturing images, which may be stored locally, '
                    . 'transmitted to another location, or both.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $this->createCategory($categoryData, self::CATEGORY_PHOTO);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'A printer is a peripheral which makes a persistent human readable representation of graphics or text on paper '
                    . 'or similar physical media.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $this->createCategory($categoryData, self::CATEGORY_PRINTERS);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'A personal computer (PC) is a general-purpose computer whose size, capabilities, and original sale price '
                    . 'make it useful for individuals, and is intended to be operated directly by an end-user with no intervening computer '
                    . 'time-sharing models that allowed larger, more expensive minicomputer and mainframe systems to be used by many people, '
                    . 'usually at the same time.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $this->createCategory($categoryData, self::CATEGORY_PC);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'A telephone is a telecommunications device that permits two or more users to conduct a conversation when they are '
                    . 'too far apart to be heard directly. A telephone converts sound, typically and most efficiently the human voice, '
                    . 'into electronic signals suitable for transmission via cables or other transmission media over long distances, '
                    . 'and replays such signals simultaneously in audible form to its user.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $this->createCategory($categoryData, self::CATEGORY_PHONES);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'Coffeemakers or coffee machines are cooking appliances used to brew coffee. While there are many different types '
                    . 'of coffeemakers using a number of different brewing principles, in the most common devices, coffee grounds '
                    . 'are placed in a paper or metal filter inside a funnel, which is set over a glass or ceramic coffee pot, '
                    . 'a cooking pot in the kettle family. Cold water is poured into a separate chamber, which is than heated up to the '
                    . 'boiling point, and directed into the funnel.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $this->createCategory($categoryData, self::CATEGORY_COFFEE);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'A book is a set of written, printed, illustrated, or blank sheets, made of ink, paper, parchment, or other '
                    . 'materials, fastened together to hinge at one side. A single sheet within a book is a leaf, and each side of a leaf '
                    . 'is a page. A set of text-filled or illustrated pages produced in electronic format is known as an electronic book, '
                    . 'or e-book.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $categoryData->parent = $rootCategory;
        $this->createCategory($categoryData, self::CATEGORY_BOOKS);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Toys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'A toy is an item that can be used for play. Toys are generally played with by children and pets. '
                    . 'Playing with toys is an enjoyable means of training young children for life in society. Different materials are '
                    . 'used to make toys enjoyable to all ages.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $categoryData->automatedFilters = [
            OnStockCategoryAutomatedFilter::DATABASE_VALUE,
            NewProductsCategoryAutomatedFilter::DATABASE_VALUE,
        ];
        $this->createCategory($categoryData, self::CATEGORY_TOYS);

        $categoryData->automatedFilters = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Garden tools', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'A garden tool is any one of many tools made for gardens and gardening and overlaps with the range of tools '
                    . 'made for agriculture and horticulture. Garden tools can also be hand tools and power tools.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $this->createCategory($categoryData, self::CATEGORY_GARDEN_TOOLS);

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t(
                'Food is any substance consumed to provide nutritional support for the body. It is usually of plant or '
                    . 'animal origin, and contains essential nutrients, such as fats, proteins, vitamins, or minerals. The substance '
                    . 'is ingested by an organism and assimilated by the organism\'s cells to provide energy, maintain life, '
                    . 'or stimulate growth.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
        }
        $this->createCategory($categoryData, self::CATEGORY_FOOD);

        $this->createSubcategories();
    }

    private function createSubcategories(): void
    {
        $subcategoryNamesByLocale = [];

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            foreach ($this->getSubcategoryNames($domainConfig->getLocale()) as $referenceName => $translatedName) {
                $subcategoryNamesByLocale[$referenceName][$domainConfig->getLocale()] = $translatedName;
            }
        }

        foreach ($this->getSubcategoryParentReferences() as $referenceName => $parentReferenceName) {
            $categoryData = $this->categoryDataFactory->create();
            $categoryData->name = $subcategoryNamesByLocale[$referenceName];
            $categoryData->parent = $this->getReference($parentReferenceName, Category::class);

            $this->createCategory($categoryData, $referenceName);
        }
    }

    /**
     * @return array<string, string>
     */
    private function getSubcategoryNames(string $locale): array
    {
        return [
            self::CATEGORY_LAPTOPS => t('Laptops', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_DESKTOP_COMPUTERS => t('Desktop computers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_COMPUTER_ACCESSORIES => t('Computer accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_TELEVISIONS => t('Televisions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_HEADPHONES => t('Headphones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_HOME_CINEMA_SYSTEMS => t('Home cinema systems', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_DIGITAL_CAMERAS => t('Digital cameras', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_CAMERA_LENSES => t('Camera lenses', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_CAMERA_ACCESSORIES => t('Camera accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_INKJET_PRINTERS => t('Inkjet printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_LASER_PRINTERS => t('Laser printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_PRINTER_SUPPLIES => t('Printer supplies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_SMARTPHONES => t('Smartphones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_MOBILE_PHONE_ACCESSORIES => t('Mobile phone accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_SMARTWATCHES => t('Smartwatches', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_AUTOMATIC_COFFEE_MACHINES => t('Automatic coffee machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_CAPSULE_COFFEE_MACHINES => t('Capsule coffee machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_COFFEE_GRINDERS => t('Coffee grinders', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_FICTION => t('Fiction', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_NON_FICTION => t('Non-fiction', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_CHILDRENS_BOOKS => t('Children\'s books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_BUILDING_SETS => t('Building sets', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_BOARD_GAMES => t('Board games', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_OUTDOOR_TOYS => t('Outdoor toys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_HAND_TOOLS => t('Hand tools', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_POWER_TOOLS => t('Power tools', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_WATERING_SYSTEMS => t('Watering systems', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_SNACKS => t('Snacks', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_COFFEE_AND_TEA => t('Coffee & tea', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::CATEGORY_PANTRY_STAPLES => t('Pantry staples', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getSubcategoryParentReferences(): array
    {
        return [
            self::CATEGORY_LAPTOPS => self::CATEGORY_PC,
            self::CATEGORY_DESKTOP_COMPUTERS => self::CATEGORY_PC,
            self::CATEGORY_COMPUTER_ACCESSORIES => self::CATEGORY_PC,
            self::CATEGORY_TELEVISIONS => self::CATEGORY_TV,
            self::CATEGORY_HEADPHONES => self::CATEGORY_TV,
            self::CATEGORY_HOME_CINEMA_SYSTEMS => self::CATEGORY_TV,
            self::CATEGORY_DIGITAL_CAMERAS => self::CATEGORY_PHOTO,
            self::CATEGORY_CAMERA_LENSES => self::CATEGORY_PHOTO,
            self::CATEGORY_CAMERA_ACCESSORIES => self::CATEGORY_PHOTO,
            self::CATEGORY_INKJET_PRINTERS => self::CATEGORY_PRINTERS,
            self::CATEGORY_LASER_PRINTERS => self::CATEGORY_PRINTERS,
            self::CATEGORY_PRINTER_SUPPLIES => self::CATEGORY_PRINTERS,
            self::CATEGORY_SMARTPHONES => self::CATEGORY_PHONES,
            self::CATEGORY_MOBILE_PHONE_ACCESSORIES => self::CATEGORY_PHONES,
            self::CATEGORY_SMARTWATCHES => self::CATEGORY_PHONES,
            self::CATEGORY_AUTOMATIC_COFFEE_MACHINES => self::CATEGORY_COFFEE,
            self::CATEGORY_CAPSULE_COFFEE_MACHINES => self::CATEGORY_COFFEE,
            self::CATEGORY_COFFEE_GRINDERS => self::CATEGORY_COFFEE,
            self::CATEGORY_FICTION => self::CATEGORY_BOOKS,
            self::CATEGORY_NON_FICTION => self::CATEGORY_BOOKS,
            self::CATEGORY_CHILDRENS_BOOKS => self::CATEGORY_BOOKS,
            self::CATEGORY_BUILDING_SETS => self::CATEGORY_TOYS,
            self::CATEGORY_BOARD_GAMES => self::CATEGORY_TOYS,
            self::CATEGORY_OUTDOOR_TOYS => self::CATEGORY_TOYS,
            self::CATEGORY_HAND_TOOLS => self::CATEGORY_GARDEN_TOOLS,
            self::CATEGORY_POWER_TOOLS => self::CATEGORY_GARDEN_TOOLS,
            self::CATEGORY_WATERING_SYSTEMS => self::CATEGORY_GARDEN_TOOLS,
            self::CATEGORY_SNACKS => self::CATEGORY_FOOD,
            self::CATEGORY_COFFEE_AND_TEA => self::CATEGORY_FOOD,
            self::CATEGORY_PANTRY_STAPLES => self::CATEGORY_FOOD,
        ];
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    private function createCategory(CategoryData $categoryData, string $referenceName): Category
    {
        $categoryData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $referenceName)->toString();

        /** @var \App\Model\Category\Category $category */
        $category = $this->categoryFacade->create($categoryData);

        $this->addReference($referenceName, $category);

        return $category;
    }
}
