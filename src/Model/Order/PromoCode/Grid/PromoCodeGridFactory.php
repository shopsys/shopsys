<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\Grid;

use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeLimit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\ArrayDataSource;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory as BasePromoCodeGridFactory;

class PromoCodeGridFactory extends BasePromoCodeGridFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(EntityManagerInterface $em, GridFactory $gridFactory, AdminDomainTabsFacade $adminDomainTabsFacade)
    {
        parent::__construct($em, $gridFactory);
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @param bool $withEditButton
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create($withEditButton = true)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('pc')
            ->from(PromoCode::class, 'pc')
            ->where('pc.domainId = :domainId')
            ->setParameter('domainId', $this->adminDomainTabsFacade->getSelectedDomainId());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pc.id');

        $grid = $this->gridFactory->create('promoCodeList', $dataSource);
        $grid->setDefaultOrder('code');
        $grid->addColumn('code', 'pc.code', t('Code'), true);
        //$grid->addColumn('percent', 'pc.percent', t('Discount'), true);
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
