<?php

declare(strict_types=1);

namespace App\Model\Mail\Grid;

use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Mail\Grid\MailTemplateGridFactory as BaseMailTemplateGridFactory;

/**
 * @property \App\Model\Mail\MailTemplateRepository $mailTemplateRepository
 * @method __construct(\App\Model\Mail\MailTemplateRepository $mailTemplateRepository, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade, \Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration $mailTemplateConfiguration)
 */
class MailTemplateGridFactory extends BaseMailTemplateGridFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $grid = parent::create();

        $grid->addColumn('transport', 'transportName', t('Doprava'), true);
        $grid->addColumn('payment', 'paymentName', t('Platba'), true);
        $grid->addColumn('orderStockStatus', 'mt.orderStockStatus', t('Skladovost'), true);
        $grid->addDeleteActionColumn('admin_mail_deletetemplate', ['id' => 'mt.id']);

        $grid->setTheme(
            '@ShopsysFramework/Admin/Content/Mail/listGrid.html.twig',
            [
                'readableNames' => $this->mailTemplateConfiguration->getReadableNamesIndexedBySlug(),
                'orderStockStatusesTranslations' => Order::getAllStockStatusesTranslations(),
            ]
        );

        return $grid;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    protected function createDataSource(): DataSourceInterface
    {
        $queryBuilder = $this->mailTemplateRepository->createGridQueryBuilder($this->adminDomainTabsFacade->getSelectedDomainId());

        return new QueryBuilderDataSource($queryBuilder, 'mt.id');
    }
}
