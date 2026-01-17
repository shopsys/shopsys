<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Grid;

use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;

class PaymentGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly PaymentRepository $paymentRepository,
        protected readonly Localization $localization,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->paymentRepository->getQueryBuilderForAll()
            ->addSelect('pt')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameter('locale', $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale());
        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
            $queryBuilder,
            'p.id',
            function ($row) {
                $payment = $this->paymentRepository->findById($row['p']['id']);
                $row['displayPrice'] = $this->getDisplayPrice($payment);
                $row['domainId'] = $this->adminDomainTabsFacade->getSelectedDomainId();

                return $row;
            },
        );

        $grid = $this->gridFactory->create('paymentList', $dataSource, $roleConstant);
        $grid->enableDragAndDrop(Payment::class);

        $grid->addColumn('name', 'pt.name', t('Name'));
        $grid->addColumn('price', 'displayPrice', t('Price'))->setClassAttribute('w-25 text-end');

        $grid->addEditActionColumn('admin_payment_edit', ['id' => 'p.id']);
        $grid->addDeleteActionColumn('admin_payment_delete', ['id' => 'p.id'])
            ->setConfirmMessage(t('Do you really want to remove this payment?'));

        $grid->setTheme('@ShopsysAdministration/content/payment/listGrid.html.twig');

        return $grid;
    }

    /**
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
