<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations;

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
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface
     */
    protected $categoryDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations
     */
    private $dataFixturesTranslations;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface $categoryDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslations $dataFixturesTranslations
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        CategoryDataFactoryInterface $categoryDataFactory,
        Domain $domain,
        DataFixturesTranslations $dataFixturesTranslations
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->categoryDataFactory = $categoryDataFactory;
        $this->domain = $domain;
        $this->dataFixturesTranslations = $dataFixturesTranslations;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /**
         * Root category is created in database migration.
         * @see \Shopsys\FrameworkBundle\Migrations\Version20180603135345
         */
        $rootCategory = $this->categoryFacade->getRootCategory();
        $categoryData = $this->categoryDataFactory->create();
        $emptyDescriptionsForAllDomains = $this->createDomainKeyedArray();

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_ELECTRONICS
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_ELECTRONICS,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $categoryData->parent = $rootCategory;
        $this->createCategory($categoryData, self::CATEGORY_ELECTRONICS);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_TV
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_TV,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        /** @var \Shopsys\ShopBundle\Model\Category\Category $categoryElectronics */
        $categoryElectronics = $this->getReference(self::CATEGORY_ELECTRONICS);
        $categoryData->parent = $categoryElectronics;
        $this->createCategory($categoryData, self::CATEGORY_TV);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_PHOTO
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_PHOTO,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $this->createCategory($categoryData, self::CATEGORY_PHOTO);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_PRINTERS
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_PRINTERS,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $this->createCategory($categoryData, self::CATEGORY_PRINTERS);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_PC
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_PC,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $this->createCategory($categoryData, self::CATEGORY_PC);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_PHONES
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_PHONES,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $this->createCategory($categoryData, self::CATEGORY_PHONES);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_COFFEE
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_COFFEE,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $this->createCategory($categoryData, self::CATEGORY_COFFEE);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_BOOKS
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_BOOKS,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $categoryData->parent = $rootCategory;
        $this->createCategory($categoryData, self::CATEGORY_BOOKS);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_TOYS
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_TOYS,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $this->createCategory($categoryData, self::CATEGORY_TOYS);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_GARDEN_TOOLS
        );
        $categoryData->descriptions = array_replace(
            $emptyDescriptionsForAllDomains,
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_GARDEN_TOOLS,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $this->createCategory($categoryData, self::CATEGORY_GARDEN_TOOLS);

        $categoryData->name = $this->dataFixturesTranslations->getEntityAttributeTranslationsByReferenceName(
            DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
            DataFixturesTranslations::TRANSLATED_ATTRIBUTE_NAME,
            self::CATEGORY_FOOD
        );
        $categoryData->descriptions = array_replace(
            $this->createDomainKeyedArray(),
            [
                Domain::FIRST_DOMAIN_ID => $this->dataFixturesTranslations->getEntityAttributeTranslationByReferenceNameForDomainId(
                    DataFixturesTranslations::TRANSLATED_ENTITY_CATEGORY,
                    DataFixturesTranslations::TRANSLATED_ATTRIBUTE_DESCRIPTION,
                    self::CATEGORY_FOOD,
                    Domain::FIRST_DOMAIN_ID
                ),
            ]
        );
        $this->createCategory($categoryData, self::CATEGORY_FOOD);
    }

    /**
     * @return null[]
     */
    protected function createDomainKeyedArray(): array
    {
        return array_fill_keys($this->domain->getAllIds(), null);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryData $categoryData
     * @param string|null $referenceName
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    protected function createCategory(CategoryData $categoryData, $referenceName = null)
    {
        $category = $this->categoryFacade->create($categoryData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $category);
        }

        return $category;
    }
}
