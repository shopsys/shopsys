<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
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

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Category\CategoryDataFactory $categoryDataFactory
     */
    public function __construct(
        private readonly CategoryFacade $categoryFacade,
        private readonly CategoryDataFactoryInterface $categoryDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
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
        $this->createCategory($categoryData, self::CATEGORY_TOYS);

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
        $categoryGardenTools = $this->createCategory($categoryData, self::CATEGORY_GARDEN_TOOLS);

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
        $categoryFood = $this->createCategory($categoryData, self::CATEGORY_FOOD);

        $categoryData = $this->categoryDataFactory->createFromCategory($categoryElectronics);
        $categoryData->linkedCategories[] = $categoryFood;
        $categoryData->linkedCategories[] = $categoryGardenTools;
        $this->categoryFacade->edit($categoryElectronics->getId(), $categoryData);
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @param string $referenceName
     * @return \App\Model\Category\Category
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
