<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;

class NavigationItemFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemRepository $navigationItemRepository
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemCategoryFacade $navigationItemCategoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDetailFactory $navigationItemDetailFactory
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemFactory $navigationItemFactory
     */
    public function __construct(
        protected readonly EntityManagerDecorator $em,
        protected readonly NavigationItemRepository $navigationItemRepository,
        protected readonly NavigationItemCategoryFacade $navigationItemCategoryFacade,
        protected readonly NavigationItemDetailFactory $navigationItemDetailFactory,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
        protected readonly NavigationItemFactory $navigationItemFactory,
    ) {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedItemsQueryBuilder(): QueryBuilder
    {
        return $this->navigationItemRepository->getOrderedItemsQueryBuilder();
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedItemsByDomainQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getOrderedItemsQueryBuilder()->where('ni.domainId = :domainId')
            ->setParameter(':domainId', $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDetail[]
     */
    public function getOrderedNavigationItemDetails(DomainConfig $domainConfig): array
    {
        $navigationItems = $this->getOrderedItemsByDomainQueryBuilder($domainConfig->getId())->getQuery()->execute();

        return $this->navigationItemDetailFactory->createDetails($navigationItems, $domainConfig);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem
     */
    public function getById(int $id): NavigationItem
    {
        return $this->navigationItemRepository->getById($id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData $navigationItemData
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem
     */
    public function create(NavigationItemData $navigationItemData): NavigationItem
    {
        $this->fixUrlInNavigationItemData($navigationItemData);

        $navigationItem = $this->navigationItemFactory->create($navigationItemData);

        $this->em->persist($navigationItem);
        $this->em->flush();

        $this->navigationItemCategoryFacade
            ->refreshCategoriesForNavigationItem($navigationItem, $navigationItemData);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);

        return $navigationItem;
    }

    /**
     * @param int $id
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData $navigationItemData
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem
     */
    public function edit(int $id, NavigationItemData $navigationItemData): NavigationItem
    {
        $navigationItem = $this->getById($id);
        $this->fixUrlInNavigationItemData($navigationItemData);

        $navigationItem->edit($navigationItemData);

        $this->em->flush();

        $this->navigationItemCategoryFacade
            ->refreshCategoriesForNavigationItem($navigationItem, $navigationItemData);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);

        return $navigationItem;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData $navigationItemData
     */
    protected function fixUrlInNavigationItemData(NavigationItemData $navigationItemData): void
    {
        if ($navigationItemData->url === null) {
            return;
        }

        if (strpos($navigationItemData->url, 'http') === 0) {
            return;
        }

        if (strpos($navigationItemData->url, 'www') === 0) {
            return;
        }

        if (strpos($navigationItemData->url, '/') !== 0) {
            $navigationItemData->url = '/' . $navigationItemData->url;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Navigation\NavigationItem $navigationItem
     */
    public function delete(NavigationItem $navigationItem): void
    {
        $this->em->remove($navigationItem);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);
    }
}
