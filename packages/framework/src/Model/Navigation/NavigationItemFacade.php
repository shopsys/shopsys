<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\UrlNormalizer;

class NavigationItemFacade
{
    public function __construct(
        protected readonly EntityManagerDecorator $em,
        protected readonly NavigationItemRepository $navigationItemRepository,
        protected readonly NavigationItemCategoryFacade $navigationItemCategoryFacade,
        protected readonly NavigationItemDetailFactory $navigationItemDetailFactory,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
        protected readonly NavigationItemFactory $navigationItemFactory,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly Domain $domain,
    ) {
    }

    public function getOrderedItemsQueryBuilder(): QueryBuilder
    {
        return $this->navigationItemRepository->getOrderedItemsQueryBuilder();
    }

    public function getOrderedItemsByDomainQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getOrderedItemsQueryBuilder()->where('ni.domainId = :domainId')
            ->setParameter(':domainId', $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Navigation\NavigationItemDetail[]
     */
    public function getOrderedNavigationItemDetails(DomainConfig $domainConfig): array
    {
        $navigationItems = $this->getOrderedItemsByDomainQueryBuilder($domainConfig->getId())->getQuery()->getResult();

        return $this->navigationItemDetailFactory->createDetails($navigationItems, $domainConfig);
    }

    public function getById(int $id): NavigationItem
    {
        return $this->navigationItemRepository->getById($id);
    }

    public function create(NavigationItemData $navigationItemData): NavigationItem
    {
        $this->fixUrlInNavigationItemData($navigationItemData);

        $navigationItem = $this->navigationItemFactory->create($navigationItemData);
        $this->setNavigationItemRouteName($navigationItem);

        $this->em->persist($navigationItem);
        $this->em->flush();

        $this->navigationItemCategoryFacade
            ->refreshCategoriesForNavigationItem($navigationItem, $navigationItemData);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);

        return $navigationItem;
    }

    public function edit(int $id, NavigationItemData $navigationItemData): NavigationItem
    {
        $navigationItem = $this->getById($id);
        $this->fixUrlInNavigationItemData($navigationItemData);

        $navigationItem->edit($navigationItemData);
        $this->setNavigationItemRouteName($navigationItem);

        $this->em->flush();

        $this->navigationItemCategoryFacade
            ->refreshCategoriesForNavigationItem($navigationItem, $navigationItemData);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);

        return $navigationItem;
    }

    protected function fixUrlInNavigationItemData(NavigationItemData $navigationItemData): void
    {
        if ($navigationItemData->type !== NavigationItemTypeEnum::LINK) {
            $navigationItemData->url = null;

            return;
        }

        if ($navigationItemData->url === null) {
            return;
        }

        $domainConfig = $this->domain->getDomainConfigById($navigationItemData->domainId);
        $navigationItemData->url = UrlNormalizer::normalizeUrl($navigationItemData->url, $domainConfig);
    }

    public function delete(NavigationItem $navigationItem): void
    {
        $this->em->remove($navigationItem);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::NAVIGATION_QUERY_KEY_PART);
    }

    protected function setNavigationItemRouteName(NavigationItem $navigationItem): void
    {
        if ($navigationItem->getType() !== NavigationItemTypeEnum::LINK || $navigationItem->getUrl() === null) {
            $navigationItem->setRouteName(null);

            return;
        }

        $friendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug($navigationItem->getDomainId(), trim($navigationItem->getUrl(), '/'));
        $navigationItem->setRouteName($friendlyUrl?->getRouteName());
    }
}
