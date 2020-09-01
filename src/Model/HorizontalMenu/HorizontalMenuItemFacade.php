<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

use App\Component\Cache\TwigCachedMenuFacade;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;

class HorizontalMenuItemFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    private $em;

    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemRepository
     */
    private $horizontalMenuItemRepository;

    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemCategoryFacade
     */
    private $horizontalMenuItemCategoryFacade;

    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemDetailFactory
     */
    private $horizontalMenuItemDetailFactory;

    /**
     * @var \App\Component\Cache\TwigCachedMenuFacade
     */
    private $twigCachedMenuFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $entityManager
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemRepository $horizontalMenuItemRepository
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemCategoryFacade $horizontalMenuItemCategoryFacade
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemDetailFactory $horizontalMenuItemDetailFactory
     * @param \App\Component\Cache\TwigCachedMenuFacade $twigCachedMenuFacade
     */
    public function __construct(
        EntityManagerDecorator $entityManager,
        HorizontalMenuItemRepository $horizontalMenuItemRepository,
        HorizontalMenuItemCategoryFacade $horizontalMenuItemCategoryFacade,
        HorizontalMenuItemDetailFactory $horizontalMenuItemDetailFactory,
        TwigCachedMenuFacade $twigCachedMenuFacade
    ) {
        $this->em = $entityManager;
        $this->horizontalMenuItemRepository = $horizontalMenuItemRepository;
        $this->horizontalMenuItemCategoryFacade = $horizontalMenuItemCategoryFacade;
        $this->horizontalMenuItemDetailFactory = $horizontalMenuItemDetailFactory;
        $this->twigCachedMenuFacade = $twigCachedMenuFacade;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedItemsQueryBuilder(): QueryBuilder
    {
        return $this->horizontalMenuItemRepository->getOrderedItemsQueryBuilder();
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedItemsByDomainQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->getOrderedItemsQueryBuilder()->where('hmi.domainId = :domainId')
            ->setParameter(':domainId', $domainId);
    }

    /**
     * @param int $domainId
     * @return \App\Model\HorizontalMenu\HorizontalMenuItemDetail[]
     */
    public function getOrderedHorizontalMenuItemDetails(int $domainId): array
    {
        $horizontalMenuItems = $this->getOrderedItemsByDomainQueryBuilder($domainId)->getQuery()->execute();

        return $this->horizontalMenuItemDetailFactory->createDetails($horizontalMenuItems, $domainId);
    }

    /**
     * @param int $id
     * @return \App\Model\HorizontalMenu\HorizontalMenuItem
     */
    public function getById(int $id): HorizontalMenuItem
    {
        return $this->horizontalMenuItemRepository->getById($id);
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     * @return \App\Model\HorizontalMenu\HorizontalMenuItem
     */
    public function create(HorizontalMenuItemData $horizontalMenuItemData): HorizontalMenuItem
    {
        $this->fixUrlInHorizontalMenuItemData($horizontalMenuItemData);

        $horizontalMenuItem = new HorizontalMenuItem($horizontalMenuItemData);

        $this->em->persist($horizontalMenuItem);
        $this->em->flush($horizontalMenuItem);

        $this->horizontalMenuItemCategoryFacade
            ->refreshCategoriesForHorizontalMenuItem($horizontalMenuItem, $horizontalMenuItemData);

        $this->twigCachedMenuFacade->invalidateCachedMenuByDomainId($horizontalMenuItem->getDomainId());

        return $horizontalMenuItem;
    }

    /**
     * @param int $id
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     * @return \App\Model\HorizontalMenu\HorizontalMenuItem
     */
    public function edit(int $id, HorizontalMenuItemData $horizontalMenuItemData): HorizontalMenuItem
    {
        $horizontalMenuItem = $this->getById($id);
        $this->fixUrlInHorizontalMenuItemData($horizontalMenuItemData);

        $horizontalMenuItem->edit($horizontalMenuItemData);

        $this->em->flush($horizontalMenuItem);

        $this->horizontalMenuItemCategoryFacade
            ->refreshCategoriesForHorizontalMenuItem($horizontalMenuItem, $horizontalMenuItemData);

        $this->twigCachedMenuFacade->invalidateCachedMenuByDomainId($horizontalMenuItem->getDomainId());

        return $horizontalMenuItem;
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemData $horizontalMenuItemData
     */
    private function fixUrlInHorizontalMenuItemData(HorizontalMenuItemData $horizontalMenuItemData): void
    {
        if ($horizontalMenuItemData->url === null) {
            return;
        }

        if (strpos($horizontalMenuItemData->url, 'http') === 0) {
            return;
        }

        if (strpos($horizontalMenuItemData->url, 'www') === 0) {
            return;
        }

        if (strpos($horizontalMenuItemData->url, '/') !== 0) {
            $horizontalMenuItemData->url = '/' . $horizontalMenuItemData->url;
        }
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     */
    public function delete(HorizontalMenuItem $horizontalMenuItem): void
    {
        $this->twigCachedMenuFacade->invalidateCachedMenuByDomainId($horizontalMenuItem->getDomainId());
        $this->em->remove($horizontalMenuItem);
        $this->em->flush($horizontalMenuItem);
    }
}
