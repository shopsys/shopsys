<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository;

class PromoCodeGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly PromoCodeLimitRepository $promoCodeLimitRepository,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     *@throws \Shopsys\FrameworkBundle\Component\Grid\Exception\EmptyGridIdException
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     */
    #[Override]
    public function create(?string $roleConstant, bool $withEditButton = true, ?string $search = null): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('pc')
            ->from(PromoCode::class, 'pc')
            ->where('pc.domainId = :domainId')
            ->setParameter('domainId', $this->adminDomainTabsFacade->getSelectedDomainId());

        $manipulator = function ($row) {
            $now = $this->clock->now();

            $row['pc']['isActive'] = $row['pc']['enabled'] &&
                ($row['pc']['datetimeValidFrom'] === null || $row['pc']['datetimeValidFrom'] <= $now) &&
                ($row['pc']['datetimeValidTo'] === null || $row['pc']['datetimeValidTo'] >= $now) &&
                ($row['pc']['remainingUses'] === null || $row['pc']['remainingUses'] > 0);


            $row['pc']['percent'] = $this->getLimitsByPromoCodeId($row['pc']['id']);

            return $row;
        };

        if ($search !== null) {
            $queryBuilder
                ->andWhere('LOWER(pc.code) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create($queryBuilder, 'pc.id', $manipulator);

        $grid = $this->gridFactory->create('promoCodeList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('code');
        $grid->addColumn('code', 'pc.code', t('Code'), true);
        $grid->addColumn('active', 'pc.enabled', t('Active'), true);
        $grid->addColumn('percent', 'pc.percent', t('Discount'));
        $grid->addColumn('prefix', 'pc.prefix', t('Prefix'));

        if ($withEditButton === true) {
            $grid->addEditActionColumn('admin_promocode_edit', ['id' => 'pc.id']);
        }

        $grid->addDeleteActionColumn('admin_promocode_delete', ['id' => 'pc.id'])
            ->setConfirmMessage(t('Do you really want to remove this promo code?'));

        $grid->addActionColumn('duplicate', t('Duplicate'), 'admin_promocode_new', ['fillFromPromoCodeId' => 'pc.id']);

        $grid->setTheme('@ShopsysAdministration/content/promoCode/listGrid.html.twig');

        return $grid;
    }

    /**
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
