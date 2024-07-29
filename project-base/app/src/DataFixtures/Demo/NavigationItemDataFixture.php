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
            $this->addCategoriesToNavigationItem($navigationItemData);
            $this->createItem($navigationItemData);

            $navigationItemData = $this->navigationItemDataFactory->createNew();
            $navigationItemData->name = t('Gadgets', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $navigationItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_ELECTRONICS,
                $domainId,
            );
            $navigationItemData->domainId = $domainId;
            $this->createItem($navigationItemData);

            $navigationItemData = $this->navigationItemDataFactory->createNew();
            $navigationItemData->name = t('Bookworm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $navigationItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_BOOKS,
                $domainId,
            );
            $navigationItemData->domainId = $domainId;
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
     */
    private function addCategoriesToNavigationItem(NavigationItemData $navigationItemData): void
    {
        $navigationItemData->categoriesByColumnNumber[1] = [
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_BOOKS),
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_TOYS),
        ];
        $navigationItemData->categoriesByColumnNumber[2] = [
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS),
        ];
        $navigationItemData->categoriesByColumnNumber[3] = [
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_FOOD),
        ];
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
