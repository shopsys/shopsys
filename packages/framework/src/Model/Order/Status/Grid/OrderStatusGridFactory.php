<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;

class OrderStatusGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
        protected readonly ProductReviewEnabledChecker $productReviewEnabledChecker,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('os, ost')
            ->from(OrderStatus::class, 'os')
            ->join('os.translations', 'ost', Join::WITH, 'ost.locale = :locale')
            ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities());
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'os.id');

        $grid = $this->gridFactory->create('orderStatusList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'ost.name', t('Name'), true);

        if ($this->productReviewEnabledChecker->isEnabledOnAnyDomain()) {
            $grid->addColumn('productReviewsAllowed', 'os.productReviewsAllowed', t('Allow product reviews'), true);
        }

        $grid->addDeleteActionColumn('admin_orderstatus_deleteconfirm', ['id' => 'os.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAdministration/content/orderStatus/listGrid.html.twig', [
            'TYPE_NEW' => OrderStatusTypeEnum::TYPE_NEW,
            'TYPE_DONE' => OrderStatusTypeEnum::TYPE_DONE,
            'TYPE_CANCELED' => OrderStatusTypeEnum::TYPE_CANCELED,
            'TYPE_WITHDRAWN' => OrderStatusTypeEnum::TYPE_WITHDRAWN,
        ]);

        return $grid;
    }
}
