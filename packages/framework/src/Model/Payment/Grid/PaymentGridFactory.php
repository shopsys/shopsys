<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Grid;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;

class PaymentGridFactory implements GridFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository $paymentRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly PaymentRepository $paymentRepository,
        protected readonly Localization $localization,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create()
    {
        $queryBuilder = $this->paymentRepository->getQueryBuilderForAll()
            ->addSelect('pt')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameter('locale', $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale());
        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'p.id',
            function ($row) {
                $payment = $this->paymentRepository->findById($row['p']['id']);
                $row['displayPrice'] = $this->getDisplayPrice($payment);
                $row['domainId'] = $this->adminDomainTabsFacade->getSelectedDomainId();

                return $row;
            },
        );

        $grid = $this->gridFactory->create('paymentList', $dataSource);
        $grid->enableDragAndDrop(Payment::class);

        $grid->addColumn('name', 'pt.name', t('Name'));
        $grid->addColumn('price', 'displayPrice', t('Price'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_payment_edit', ['id' => 'p.id']);
        $grid->addDeleteActionColumn('admin_payment_delete', ['id' => 'p.id'])
            ->setConfirmMessage(t('Do you really want to remove this payment?'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Payment/listGrid.html.twig');

        return $grid;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getDisplayPrice(Payment $payment)
    {
        $transportBasePricesIndexedByDomainId = $this->paymentFacade->getIndependentBasePricesIndexedByDomainId(
            $payment,
        );
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        return $transportBasePricesIndexedByDomainId[$domainId]->getPriceWithVat();
    }
}
