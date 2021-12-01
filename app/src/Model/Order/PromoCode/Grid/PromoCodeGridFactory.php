<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\Grid;

use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeLimit;
use App\Model\Order\PromoCode\PromoCodeLimitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid\PromoCodeGridFactory as BasePromoCodeGridFactory;

class PromoCodeGridFactory extends BasePromoCodeGridFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitRepository
     */
    private PromoCodeLimitRepository $promoCodeLimitRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \App\Model\Order\PromoCode\PromoCodeLimitRepository $promoCodeLimitRepository
     */
    public function __construct(EntityManagerInterface $em, GridFactory $gridFactory, AdminDomainTabsFacade $adminDomainTabsFacade, PromoCodeLimitRepository $promoCodeLimitRepository)
    {
        parent::__construct($em, $gridFactory);

        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->promoCodeLimitRepository = $promoCodeLimitRepository;
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
        $manipulator = function ($row) {
            $row['pc']['percent'] = $this->getLimitsByPromoCodeId($row['pc']['id']);

            return $row;
        };
        $dataSource = new QueryBuilderWithRowManipulatorDataSource($queryBuilder, 'pc.id', $manipulator);

        $grid = $this->gridFactory->create('promoCodeList', $dataSource);
        $grid->setDefaultOrder('code');
        $grid->addColumn('code', 'pc.code', t('Code'), true);
        $grid->addColumn('percent', 'pc.percent', t('Discount'));
        $grid->addColumn('prefix', 'pc.prefix', t('Prefix'));
        $grid->setActionColumnClassAttribute('table-col table-col-10');

        if ($withEditButton === true) {
            $grid->addEditActionColumn('admin_promocode_edit', ['id' => 'pc.id']);
        }

        $grid->addDeleteActionColumn('admin_promocode_delete', ['id' => 'pc.id'])
            ->setConfirmMessage(t('Do you really want to remove this promo code?'));

        $grid->addActionColumn('document-copy', t('Duplikovat'), 'admin_promocode_new', ['fillFromPromoCodeId' => 'pc.id']);

        $grid->setTheme('Admin/Content/PromoCode/listGrid.html.twig');

        return $grid;
    }

    /**
     * @param int $id
     * @return string[]|null[]
     */
    private function getLimitsByPromoCodeId(int $id): array
    {
        $limits = $this->promoCodeLimitRepository->getLimitsByPromoCodeId($id);
        $flatten = static function (PromoCodeLimit $limit) {
            return $limit->getDiscount();
        };

        return array_map($flatten, $limits);
    }
}
