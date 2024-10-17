<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDataFactory;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NavigationItemDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemFacade $navigationItemFacade
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDataFactory $navigationItemDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        private readonly NavigationItemFacade $navigationItemFacade,
        private readonly NavigationItemDataFactory $navigationItemDataFactory,
        private readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $navigationItemData = $this->navigationItemDataFactory->createNew();
            $navigationItemData->name = t('Catalog', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $navigationItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_ELECTRONICS,
                $domainId,
            );
            $navigationItemData->domainId = $domainId;

            $categoriesForCatalog = [
                1 => [
                    CategoryDataFixture::CATEGORY_ELECTRONICS,
                    CategoryDataFixture::CATEGORY_BOOKS,
                    CategoryDataFixture::CATEGORY_TOYS,
                ],
                2 => [
                    CategoryDataFixture::CATEGORY_GARDEN_TOOLS,
                ],
                3 => [
                    CategoryDataFixture::CATEGORY_FOOD,
                ],
            ];
            $this->addCategoriesToNavigationItem($navigationItemData, $categoriesForCatalog);

            $this->createItem($navigationItemData);

            $navigationItemData = $this->navigationItemDataFactory->createNew();
            $navigationItemData->name = t('Gadgets', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $navigationItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_ELECTRONICS,
                $domainId,
            );
            $navigationItemData->domainId = $domainId;

            $categoriesForGadgets = [
                1 => [
                    CategoryDataFixture::CATEGORY_PC,
                    CategoryDataFixture::CATEGORY_TV,
                ],
                2 => [
                    CategoryDataFixture::CATEGORY_PRINTERS,
                    CategoryDataFixture::CATEGORY_PHOTO,
                ],
                3 => [
                    CategoryDataFixture::CATEGORY_COFFEE,
                    CategoryDataFixture::CATEGORY_PHONES,
                ],
            ];
            $this->addCategoriesToNavigationItem($navigationItemData, $categoriesForGadgets);

            $this->createItem($navigationItemData);

            $navigationItemData = $this->navigationItemDataFactory->createNew();
            $navigationItemData->name = t('Bookworm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $navigationItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_BOOKS,
                $domainId,
            );
            $navigationItemData->domainId = $domainId;

            $categoriesForBookworm = [
                1 => [
                    CategoryDataFixture::CATEGORY_BOOKS,
                ],
                2 => [
                    CategoryDataFixture::CATEGORY_PRINTERS,
                ],
            ];
            $this->addCategoriesToNavigationItem($navigationItemData, $categoriesForBookworm);

            $this->createItem($navigationItemData);

            $navigationItemData = $this->navigationItemDataFactory->createNew();
            $navigationItemData->name = t('Growing', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $navigationItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_GARDEN_TOOLS,
                $domainId,
            );
            $navigationItemData->domainId = $domainId;
            $this->createItem($navigationItemData);

            $navigationItemData = $this->navigationItemDataFactory->createNew();
            $navigationItemData->name = t('Snack', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $navigationItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_FOOD,
                $domainId,
            );
            $navigationItemData->domainId = $domainId;

            $categoriesForSnack = [
                1 => [
                    CategoryDataFixture::CATEGORY_FOOD,
                ],
                2 => [
                    CategoryDataFixture::CATEGORY_COFFEE,
                ],
            ];
            $this->addCategoriesToNavigationItem($navigationItemData, $categoriesForSnack);

            $this->createItem($navigationItemData);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData $navigationItemData
     */
    private function createItem(NavigationItemData $navigationItemData): void
    {
        $this->navigationItemFacade->create($navigationItemData);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            CategoryDataFixture::class,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData $navigationItemData
     * @param array<int, string[]> $categoryReferenceNamesByColumn
     */
    private function addCategoriesToNavigationItem(
        NavigationItemData $navigationItemData,
        array $categoryReferenceNamesByColumn,
    ): void {
        foreach ($categoryReferenceNamesByColumn as $columnNumber => $categoryReferenceNames) {
            $navigationItemData->categoriesByColumnNumber[$columnNumber] = array_map(
                function (string $category) {
                    return $this->getCategoryReference($category);
                },
                $categoryReferenceNames,
            );
        }
    }

    /**
     * @param string $name
     * @return \App\Model\Category\Category
     */
    private function getCategoryReference(string $name): Category
    {
        return $this->getReference($name, Category::class);
    }

    /**
     * @param string $categoryReferenceName
     * @param int $domainId
     * @return string
     */
    private function generateUrlForCategoryOnDomain(string $categoryReferenceName, int $domainId): string
    {
        $router = $this->domainRouterFactory->getRouter($domainId);
        $categoryReference = $this->getCategoryReference($categoryReferenceName);

        return $router->generate(
            'front_product_list',
            ['id' => $categoryReference->getId()],
            UrlGeneratorInterface::RELATIVE_PATH,
        );
    }
}
