<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\Grid;

use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;

class TransportGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly TransportRepository $transportRepository,
        protected readonly Localization $localization,
        protected readonly TransportFacade $transportFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->transportRepository->getQueryBuilderForAll()
            ->addSelect('tt.name')
            ->addSelect('tgt.name AS transportGroupName')
            ->join('t.translations', 'tt', Join::WITH, 'tt.locale = :locale')
            ->leftJoin('t.group', 'tg')
            ->leftJoin('tg.translations', 'tgt', Join::WITH, 'tgt.locale = :locale')
            ->setParameter('locale', $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale());
        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
            $queryBuilder,
            't.id',
            function ($row) {
                $transport = $this->transportRepository->findById($row['t']['id']);
                $row['prices'] = $this->getDisplayPrices($transport);
                $row['domainId'] = $this->adminDomainTabsFacade->getSelectedDomainId();

                return $row;
            },
        );

        $grid = $this->gridFactory->create('transportList', $dataSource, $roleConstant);
        $grid->enableDragAndDrop(Transport::class);

        $grid->addColumn('name', 'tt.name', t('Name'));
        $grid->addColumn('group', 'transportGroupName', t('Group'));
        $grid->addColumn('prices', 'prices', t('Prices'))->setClassAttribute('w-25 text-end');

        $grid->addEditActionColumn('admin_transport_edit', ['id' => 't.id']);
        $grid->addDeleteActionColumn('admin_transport_delete', ['id' => 't.id'])
            ->setConfirmMessage(t('Do you really want to remove this shipping?'));

        $grid->setTheme('@ShopsysAdministration/content/transport/listGrid.html.twig');

        return $grid;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[]
     */
    protected function getDisplayPrices(Transport $transport): array
    {
        $transportBasePricesIndexedByDomainId = $this->transportFacade->getIndependentBasePricesIndexedByDomainId(
            $transport,
        );
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        return $transportBasePricesIndexedByDomainId[$domainId];
    }
}
