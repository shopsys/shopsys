<?php

declare(strict_types=1);

namespace App\Model\Navigation;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;

class NavigationItemFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \App\Model\Navigation\NavigationItemRepository $navigationItemRepository
     * @param \App\Model\Navigation\NavigationItemCategoryFacade $navigationItemCategoryFacade
     * @param \App\Model\Navigation\NavigationItemDetailFactory $navigationItemDetailFactory
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly NavigationItemRepository $navigationItemRepository,
        private readonly NavigationItemCategoryFacade $navigationItemCategoryFacade,
        private readonly NavigationItemDetailFactory $navigationItemDetailFactory,
        private readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
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
     * @return \App\Model\Navigation\NavigationItemDetail[]
     */
    public function getOrderedNavigationItemDetails(DomainConfig $domainConfig): array
    {
        $navigationItems = $this->getOrderedItemsByDomainQueryBuilder($domainConfig->getId())->getQuery()->execute();

        return $this->navigationItemDetailFactory->createDetails($navigationItems, $domainConfig);
    }

    /**
     * @param int $id
     * @return \App\Model\Navigation\NavigationItem
     */
    public function getById(int $id): NavigationItem
    {
        return $this->navigationItemRepository->getById($id);
    }

    /**
     * @param \App\Model\Navigation\NavigationItemData $navigationItemData
     * @return \App\Model\Navigation\NavigationItem
     */
    public function create(NavigationItemData $navigationItemData): NavigationItem
    {
        $this->fixUrlInNavigationItemData($navigationItemData);

        $navigationItem = new NavigationItem($navigationItemData);

        $this->em->persist($navigationItem);
        $this->em->flush();

        $this->navigationItemCategoryFacade
            ->refreshCategoriesForNavigationItem($navigationItem, $navigationItemData);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);

        return $navigationItem;
    }

    /**
     * @param int $id
     * @param \App\Model\Navigation\NavigationItemData $navigationItemData
     * @return \App\Model\Navigation\NavigationItem
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
     * @param \App\Model\Navigation\NavigationItemData $navigationItemData
     */
    private function fixUrlInNavigationItemData(NavigationItemData $navigationItemData): void
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
     * @param \App\Model\Navigation\NavigationItem $navigationItem
     */
    public function delete(NavigationItem $navigationItem): void
    {
        $this->em->remove($navigationItem);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);
    }
}
