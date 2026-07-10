<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher\Grid;

use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherCodeGenerator;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherRepository;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherStatusEnum;

class GiftVoucherGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly GiftVoucherRepository $giftVoucherRepository,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
        protected readonly GiftVoucherStatusEnum $giftVoucherStatusEnum,
        protected readonly GiftVoucherCodeGenerator $giftVoucherCodeGenerator,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(?string $roleConstant, ?string $search = null): Grid
    {
        $queryBuilder = $this->giftVoucherRepository->getQueryBuilderByDomainIdAndSearchText(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $search,
            $search !== null ? $this->giftVoucherCodeGenerator->normalizeCode($search) : null,
        );

        $statusLabelsIndexedByStatus = array_flip($this->giftVoucherStatusEnum->getAllIndexedByTranslations());
        $now = $this->clock->now();

        $manipulator = static function ($row) use ($statusLabelsIndexedByStatus, $now) {
            $row['gv']['statusLabel'] = $statusLabelsIndexedByStatus[$row['gv']['status']] ?? $row['gv']['status'];
            $row['gv']['isExpired'] = $row['gv']['status'] === GiftVoucherStatusEnum::STATUS_UNREDEEMED
                && $row['gv']['validUntil'] < $now;
            $row['domainId'] = $row['gv']['domainId'];

            return $row;
        };

        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create($queryBuilder, 'gv.id', $manipulator);

        $grid = $this->gridFactory->create('giftVoucherList', $dataSource, $roleConstant);
        $grid->addColumn('code', 'gv.code', t('Code'), true);
        $grid->addColumn('valueWithVat', 'gv.valueWithVat', t('Value'), true);
        $grid->addColumn('status', 'gv.statusLabel', t('Status'));
        $grid->addColumn('validUntil', 'gv.validUntil', t('Valid until'), true);
        $grid->addColumn('customerEmail', 'gv.customerEmail', t('Customer email'), true);

        $grid->addEditActionColumn('admin_giftvoucher_edit', ['id' => 'gv.id']);
        $grid->addActionColumn('download', t('Download PDF'), 'admin_giftvoucher_downloadpdf', ['id' => 'gv.id']);

        $grid->setTheme('@ShopsysAdministration/content/giftVoucher/listGrid.html.twig');

        return $grid;
    }
}
