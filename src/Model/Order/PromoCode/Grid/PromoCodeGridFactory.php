<?php

declare(strict_types=1);


namespace App\Model\Order\PromoCode\Grid;

use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory as BasePromoCodeGridFactory;
use App\Model\Order\PromoCode\PromoCode;


class PromoCodeGridFactory extends BasePromoCodeGridFactory
{
    /**
     * @param bool $withEditButton
     * @param int $currentDomainId
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create($withEditButton = false, $currentDomainId = 1){

        d($currentDomainId);

        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('pc')
            ->from(PromoCode::class, 'pc')
            ->where("pc.domainId = :domainId")
            ->setParameter('domainId', $currentDomainId)
        ;
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pc.id');

        $grid = $this->gridFactory->create('promoCodeList', $dataSource);
        $grid->setDefaultOrder('code');
        $grid->addColumn('code', 'pc.code', t('Code'), true);
        $grid->addColumn('percent', 'pc.percent', t('Discount'), true);
        //$grid->addColumn('domain', 'pc.domain_id', t('Doména'), true);
        $grid->setActionColumnClassAttribute('table-col table-col-10');

        if ($withEditButton === true) {
            $grid->addEditActionColumn('admin_promocode_edit', ['id' => 'pc.id']);
        }

        $grid->addDeleteActionColumn('admin_promocode_delete', ['id' => 'pc.id'])
            ->setConfirmMessage(t('Do you really want to remove this promo code?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/PromoCode/listGrid.html.twig');

        return $grid;
    }

}