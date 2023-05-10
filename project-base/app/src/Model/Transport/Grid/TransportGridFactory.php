<?php

declare(strict_types=1);

namespace App\Model\Transport\Grid;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Model\Transport\Grid\TransportGridFactory as BaseTransportGridFactory;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

/**
 * @property \App\Model\Transport\TransportRepository $transportRepository
 * @property \App\Model\Transport\TransportFacade $transportFacade
 * @method __construct(\Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \App\Model\Transport\TransportRepository $transportRepository, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \App\Model\Transport\TransportFacade $transportFacade, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade)
 * @method \Shopsys\FrameworkBundle\Component\Money\Money getDisplayPrice(\App\Model\Transport\Transport $transport)
 */
class TransportGridFactory extends BaseTransportGridFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $queryBuilder = $this->transportRepository->getQueryBuilderForAll()
            ->addSelect('tt.name')
            ->join('t.translations', 'tt', Join::WITH, 'tt.locale = :locale')
            ->setParameter('locale', $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale());
        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            't.id',
            function ($row) {
                $transport = $this->transportRepository->findById($row['t']['id']);
                $row['displayPrice'] = $this->getDisplayPrice($transport);
                $row['domainId'] = $this->adminDomainTabsFacade->getSelectedDomainId();
                return $row;
            }
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
}
