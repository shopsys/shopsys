<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Category\Category;
use App\Model\HorizontalMenu\HorizontalMenuItemData;
use App\Model\HorizontalMenu\HorizontalMenuItemDataFactory;
use App\Model\HorizontalMenu\HorizontalMenuItemFacade;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @method \App\Model\Category\Category getReference($name)
 */
class HorizontalMenuItemDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemFacade
     */
    private HorizontalMenuItemFacade $horizontalMenuItemFacade;

    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemDataFactory
     */
    private HorizontalMenuItemDataFactory $horizontalMenuItemDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    protected DomainRouterFactory $domainRouterFactory;

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemFacade $horizontalMenuItemFacade
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemDataFactory $horizontalMenuItemDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        HorizontalMenuItemFacade $horizontalMenuItemFacade,
        HorizontalMenuItemDataFactory $horizontalMenuItemDataFactory,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory
    ) {
        $this->horizontalMenuItemFacade = $horizontalMenuItemFacade;
        $this->horizontalMenuItemDataFactory = $horizontalMenuItemDataFactory;
        $this->domain = $domain;
        $this->domainRouterFactory = $domainRouterFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            $horizontalMenuItemData = $this->horizontalMenuItemDataFactory->createNew();
            $horizontalMenuItemData->name = t('Catalog', [], 'dataFixtures', $locale);
            $horizontalMenuItemData->url = '#';
            $horizontalMenuItemData->domainId = $domainId;
            $this->addCategoriesToHorizontalMenuItem($horizontalMenuItemData);
            $this->createItem($horizontalMenuItemData);

            $horizontalMenuItemData = $this->horizontalMenuItemDataFactory->createNew();
            $horizontalMenuItemData->name = t('Electronics', [], 'dataFixtures', $locale);
            $horizontalMenuItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_ELECTRONICS,
                $domainId
            );
            $horizontalMenuItemData->domainId = $domainId;
            $this->createItem($horizontalMenuItemData);

            $horizontalMenuItemData = $this->horizontalMenuItemDataFactory->createNew();
            $horizontalMenuItemData->name = t('Books', [], 'dataFixtures', $locale);
            $horizontalMenuItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_BOOKS,
                $domainId
            );
            $horizontalMenuItemData->domainId = $domainId;
            $this->createItem($horizontalMenuItemData);

            $horizontalMenuItemData = $this->horizontalMenuItemDataFactory->createNew();
            $horizontalMenuItemData->name = t('Garden tools', [], 'dataFixtures', $locale);
            $horizontalMenuItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_GARDEN_TOOLS,
                $domainId
            );
            $horizontalMenuItemData->domainId = $domainId;
            $this->createItem($horizontalMenuItemData);

            $horizontalMenuItemData = $this->horizontalMenuItemDataFactory->createNew();
            $horizontalMenuItemData->name = t('Food', [], 'dataFixtures', $locale);
            $horizontalMenuItemData->url = $this->generateUrlForCategoryOnDomain(
                CategoryDataFixture::CATEGORY_FOOD,
                $domainId
            );
            $horizontalMenuItemData->domainId = $domainId;
            $this->createItem($horizontalMenuItemData);
        }
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     */
    private function createItem(HorizontalMenuItemData $horizontalMenuItemData): void
    {
        $this->horizontalMenuItemFacade->create($horizontalMenuItemData);
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
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     */
    private function addCategoriesToHorizontalMenuItem(HorizontalMenuItemData $horizontalMenuItemData): void
    {
        $horizontalMenuItemData->categoriesByColumnNumber[1] = [
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_BOOKS),
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_TOYS),
        ];
        $horizontalMenuItemData->categoriesByColumnNumber[2] = [
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS),
        ];
        $horizontalMenuItemData->categoriesByColumnNumber[3] = [
            $this->getCategoryReference(CategoryDataFixture::CATEGORY_FOOD),
        ];
    }

    /**
     * @param string $name
     * @return \App\Model\Category\Category
     */
    private function getCategoryReference(string $name): Category
    {
        return $this->getReference($name);
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
            UrlGeneratorInterface::RELATIVE_PATH
        );
    }
}
