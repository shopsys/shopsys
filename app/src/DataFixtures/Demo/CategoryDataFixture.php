<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class CategoryDataFixture extends AbstractReferenceFixture
{
    public const CATEGORY_ELECTRONICS = 'category_electronics';
    public const CATEGORY_TV = 'category_tv';
    public const CATEGORY_PHOTO = 'category_photo';
    public const CATEGORY_PRINTERS = 'category_printers';
    public const CATEGORY_PC = 'category_pc';
    public const CATEGORY_PHONES = 'category_phones';
    public const CATEGORY_COFFEE = 'category_coffee';
    public const CATEGORY_BOOKS = 'category_books';
    public const CATEGORY_TOYS = 'category_toys';
    public const CATEGORY_GARDEN_TOOLS = 'category_garden_tools';
    public const CATEGORY_FOOD = 'category_food';

    /**
     * @var string[]
     */
    private array $uuidPool = [
        '7ba89a28-77ab-419a-b154-ef747d7a98ce',
        '57157d5a-b15b-495c-b689-65d395f1c50f',
        '8075297b-30b7-49e0-bd89-b4e5dec2f5b8',
        'c5d5a03e-1d11-43e6-9b24-9058d13346b3',
        'b4700dea-4b83-43d3-9df1-f162a114cacd',
        '8d18e212-ca21-43d0-9a61-4677bc2ed0a7',
        '6eaaa31b-0b11-45c8-bee0-0423423a799a',
        'f79ade94-3f89-4244-b424-3911a1d82a64',
        'a1c81439-e169-48f3-8432-98934b0ee2d7',
        '4b31f43c-ed0a-4db6-979b-10c27d7791f3',
        'c8154b52-af56-45cf-b8b7-36b00bf490c3',
        'dea6764b-a03b-4171-8243-46e730c8b90b',
    ];

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Model\Category\CategoryDataFactory
     */
    private $categoryDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Category\CategoryDataFactory $categoryDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        CategoryDataFactoryInterface $categoryDataFactory,
        Domain $domain
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->categoryDataFactory = $categoryDataFactory;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /**
         * Root category is created in database migration.
         *
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135345
         * @var \App\Model\Category\Category
         */
        $rootCategory = $this->categoryFacade->getRootCategory();
        $categoryData = $this->categoryDataFactory->create();

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Electronics', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('Our electronics include devices used for entertainment (flat screen TVs, DVD players, DVD movies, iPods, '
                . 'video games, remote control cars, etc.), communications (telephones, cell phones, email-capable laptops, etc.) '
                . 'and home office activities (e.g., desktop computers, printers, paper shredders, etc.).', [], 'dataFixtures', $locale);
            $categoryData->shortDescription[$domainConfig->getId()] = t('Our electronics include devices used for entertainment, communications'
                . 'and home office activities.', [], 'dataFixtures', $locale);

            $categoryData->seoH1s[$domainConfig->getId()] = t('Electronic devices', [], 'dataFixtures', $locale);
            $categoryData->seoTitles[$domainConfig->getId()] = t('Electronic stuff', [], 'dataFixtures', $locale);
            $categoryData->seoMetaDescriptions[$domainConfig->getId()] = t('All kind of electronic devices.', [], 'dataFixtures', $locale);
        }
        $categoryData->parent = $rootCategory;
        $this->createCategory($categoryData, self::CATEGORY_ELECTRONICS);

        $categoryData = $this->categoryDataFactory->create();

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('TV, audio', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('Television or TV is a telecommunication medium used for transmitting sound with moving images in monochrome '
                . '(black-and-white), or in color, and in two or three dimensions', [], 'dataFixtures', $locale);
        }
        /** @var \App\Model\Category\Category $categoryElectronics */
        $categoryElectronics = $this->getReference(self::CATEGORY_ELECTRONICS);
        $categoryData->parent = $categoryElectronics;
        $this->createCategory($categoryData, self::CATEGORY_TV);

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Cameras & Photo', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('A camera is an optical instrument for recording or capturing images, which may be stored locally, '
                . 'transmitted to another location, or both.', [], 'dataFixtures', $locale);
        }
        $this->createCategory($categoryData, self::CATEGORY_PHOTO);

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Printers', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('A printer is a peripheral which makes a persistent human readable representation of graphics or text on paper '
                . 'or similar physical media.', [], 'dataFixtures', $locale);
        }
        $this->createCategory($categoryData, self::CATEGORY_PRINTERS);

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Personal Computers & accessories', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('A personal computer (PC) is a general-purpose computer whose size, capabilities, and original sale price '
                . 'make it useful for individuals, and is intended to be operated directly by an end-user with no intervening computer '
                . 'time-sharing models that allowed larger, more expensive minicomputer and mainframe systems to be used by many people, '
                . 'usually at the same time.', [], 'dataFixtures', $locale);
        }
        $this->createCategory($categoryData, self::CATEGORY_PC);

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Mobile Phones', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('A telephone is a telecommunications device that permits two or more users to conduct a conversation when they are '
                . 'too far apart to be heard directly. A telephone converts sound, typically and most efficiently the human voice, '
                . 'into electronic signals suitable for transmission via cables or other transmission media over long distances, '
                . 'and replays such signals simultaneously in audible form to its user.', [], 'dataFixtures', $locale);
        }
        $this->createCategory($categoryData, self::CATEGORY_PHONES);

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Coffee Machines', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('Coffeemakers or coffee machines are cooking appliances used to brew coffee. While there are many different types '
                . 'of coffeemakers using a number of different brewing principles, in the most common devices, coffee grounds '
                . 'are placed in a paper or metal filter inside a funnel, which is set over a glass or ceramic coffee pot, '
                . 'a cooking pot in the kettle family. Cold water is poured into a separate chamber, which is than heated up to the '
                . 'boiling point, and directed into the funnel.', [], 'dataFixtures', $locale);
        }
        $this->createCategory($categoryData, self::CATEGORY_COFFEE);

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Books', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('A book is a set of written, printed, illustrated, or blank sheets, made of ink, paper, parchment, or other '
                . 'materials, fastened together to hinge at one side. A single sheet within a book is a leaf, and each side of a leaf '
                . 'is a page. A set of text-filled or illustrated pages produced in electronic format is known as an electronic book, '
                . 'or e-book.', [], 'dataFixtures', $locale);
        }
        $categoryData->parent = $rootCategory;
        $this->createCategory($categoryData, self::CATEGORY_BOOKS);

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Toys', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('A toy is an item that can be used for play. Toys are generally played with by children and pets. '
                . 'Playing with toys is an enjoyable means of training young children for life in society. Different materials are '
                . 'used to make toys enjoyable to all ages.', [], 'dataFixtures', $locale);
        }
        $this->createCategory($categoryData, self::CATEGORY_TOYS);

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Garden tools', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('A garden tool is any one of many tools made for gardens and gardening and overlaps with the range of tools '
                . 'made for agriculture and horticulture. Garden tools can also be hand tools and power tools.', [], 'dataFixtures', $locale);
        }
        $categoryGardenTools = $this->createCategory($categoryData, self::CATEGORY_GARDEN_TOOLS);

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $categoryData->name[$locale] = t('Food', [], 'dataFixtures', $locale);
            $categoryData->descriptions[$domainConfig->getId()] = t('Food is any substance consumed to provide nutritional support for the body. It is usually of plant or '
                . 'animal origin, and contains essential nutrients, such as fats, proteins, vitamins, or minerals. The substance '
                . 'is ingested by an organism and assimilated by the organism\'s cells to provide energy, maintain life, '
                . 'or stimulate growth.', [], 'dataFixtures', $locale);
        }
        $categoryFood = $this->createCategory($categoryData, self::CATEGORY_FOOD);

        $categoryData = $this->categoryDataFactory->createFromCategory($categoryElectronics);
        $categoryData->linkedCategories[] = $categoryFood;
        $categoryData->linkedCategories[] = $categoryGardenTools;
        $this->categoryFacade->edit($categoryElectronics->getId(), $categoryData);
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @param string|null $referenceName
     * @return \App\Model\Category\Category
     */
    private function createCategory(CategoryData $categoryData, $referenceName = null)
    {
        $categoryData->uuid = array_pop($this->uuidPool);

        /** @var \App\Model\Category\Category $category */
        $category = $this->categoryFacade->create($categoryData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $category);
        }

        return $category;
    }
}
