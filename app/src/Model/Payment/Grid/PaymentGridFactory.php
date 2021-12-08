<?php

declare(strict_types=1);

namespace App\Model\Payment\Grid;

use App\Model\Payment\Payment;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Model\Payment\Grid\PaymentGridFactory as BasePaymentGridFactory;

/**
 * @property \App\Model\Payment\PaymentRepository $paymentRepository
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 * @method __construct(\Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \App\Model\Payment\PaymentRepository $paymentRepository, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \App\Model\Payment\PaymentFacade $paymentFacade, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade)
 * @method \Shopsys\FrameworkBundle\Component\Money\Money getDisplayPrice(\App\Model\Payment\Payment $payment)
 */
class PaymentGridFactory extends BasePaymentGridFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
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
            }
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
}
