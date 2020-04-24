<?php

declare(strict_types=1);

namespace App\Model\HorizontalMenu;

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
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $entityManager
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemRepository $horizontalMenuItemRepository
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemCategoryFacade $horizontalMenuItemCategoryFacade
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemDetailFactory $horizontalMenuItemDetailFactory
     */
    public function __construct(
        EntityManagerDecorator $entityManager,
        HorizontalMenuItemRepository $horizontalMenuItemRepository,
        HorizontalMenuItemCategoryFacade $horizontalMenuItemCategoryFacade,
        HorizontalMenuItemDetailFactory $horizontalMenuItemDetailFactory
    ) {
        $this->em = $entityManager;
        $this->horizontalMenuItemRepository = $horizontalMenuItemRepository;
        $this->horizontalMenuItemCategoryFacade = $horizontalMenuItemCategoryFacade;
        $this->horizontalMenuItemDetailFactory = $horizontalMenuItemDetailFactory;
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
        $horizontalMenuItems = $this->getOrderedItemsQueryBuilder()->getQuery()->execute();

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
        $horizontalMenuItem = new HorizontalMenuItem($horizontalMenuItemData);

        $this->em->persist($horizontalMenuItem);
        $this->em->flush($horizontalMenuItem);

        $this->horizontalMenuItemCategoryFacade
            ->refreshCategoriesForHorizontalMenuItem($horizontalMenuItem, $horizontalMenuItemData);

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

        $horizontalMenuItem->edit($horizontalMenuItemData);

        $this->em->flush($horizontalMenuItem);

        $this->horizontalMenuItemCategoryFacade
            ->refreshCategoriesForHorizontalMenuItem($horizontalMenuItem, $horizontalMenuItemData);

        return $horizontalMenuItem;
    }

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItem $horizontalMenuItem
     */
    public function delete(HorizontalMenuItem $horizontalMenuItem): void
    {
        $this->em->remove($horizontalMenuItem);
        $this->em->flush($horizontalMenuItem);
    }
}
