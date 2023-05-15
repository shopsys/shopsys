<?php

namespace Shopsys\FrameworkBundle\Model\Transport\Grid;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;

class TransportGridFactory implements GridFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportRepository $transportRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly TransportRepository $transportRepository,
        protected readonly Localization $localization,
        protected readonly TransportFacade $transportFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create()
    {
        $queryBuilder = $this->transportRepository->getQueryBuilderForAll()
            ->addSelect('tt')
            ->join('t.translations', 'tt', Join::WITH, 'tt.locale = :locale')
            ->setParameter('locale', $this->localization->getAdminLocale());
        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            't.id',
            function ($row) {
                $transport = $this->transportRepository->findById($row['t']['id']);
                $row['displayPrice'] = $this->getDisplayPrice($transport);

                return $row;
            },
        );

        $grid = $this->gridFactory->create('transportList', $dataSource);
        $grid->enableDragAndDrop(Transport::class);

        $grid->addColumn('name', 'tt.name', t('Name'));
        $grid->addColumn('price', 'displayPrice', t('Price'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_transport_edit', ['id' => 't.id']);
        $grid->addDeleteActionColumn('admin_transport_delete', ['id' => 't.id'])
            ->setConfirmMessage(t('Do you really want to remove this shipping?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Transport/listGrid.html.twig');

        return $grid;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getDisplayPrice(Transport $transport)
    {
        $transportBasePricesIndexedByDomainId = $this->transportFacade->getIndependentBasePricesIndexedByDomainId(
            $transport,
        );
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        return $transportBasePricesIndexedByDomainId[$domainId]->getPriceWithVat();
    }
}
