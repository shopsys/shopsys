<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository;

class PromoCodeGridFactory implements GridFactoryInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository $promoCodeLimitRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly PromoCodeLimitRepository $promoCodeLimitRepository,
    ) {
    }

    /**
     * @param bool $withEditButton
     * @param string|null $search
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(bool $withEditButton = true, ?string $search = null): Grid
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

        if ($search !== null) {
            $queryBuilder
                ->andWhere('LOWER(pc.code) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

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

        $grid->addActionColumn('document-copy', t('Duplicate'), 'admin_promocode_new', ['fillFromPromoCodeId' => 'pc.id']);

        $grid->setTheme('@ShopsysFramework/Admin/Content/PromoCode/listGrid.html.twig');

        return $grid;
    }

    /**
     * @param int $id
     * @return string[]|null[]
     */
    protected function getLimitsByPromoCodeId(int $id): array
    {
        $limits = $this->promoCodeLimitRepository->getLimitsByPromoCodeId($id);
        $flatten = static function (PromoCodeLimit $limit) {
            return $limit->getDiscount();
        };

        return array_map($flatten, $limits);
    }
}
