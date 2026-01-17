<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\Grid;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository;

class MailTemplateGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly MailTemplateRepository $mailTemplateRepository,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly MailTemplateConfiguration $mailTemplateConfiguration,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $grid = $this->gridFactory->create('MailTemplateList', $this->createDataSource(), $roleConstant);

        $grid->addColumn('name', 'mt.name', t('Name'), true);
        $grid->addColumn('subject', 'mt.subject', t('Subject'), true);
        $grid->addColumn('orderStatus', 'orderStatusName', t('Order status'), true);
        $grid->addColumn('complaintStatus', 'complaintStatusName', t('Complaint status'), true);

        $grid->addEditActionColumn('admin_mail_edit', ['id' => 'mt.id']);

        $grid->setDefaultOrder('name');

        $grid->setTheme(
            '@ShopsysAdministration/content/mail/listGrid.html.twig',
            [
                'readableNames' => $this->mailTemplateConfiguration->getReadableNamesIndexedBySlug(),
            ],
        );

        return $grid;
    }

    protected function createDataSource(): DataSourceInterface
    {
        $queryBuilder = $this->mailTemplateRepository->createGridQueryBuilder(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
        );

        return $this->queryBuilderDataSourceFactory->create($queryBuilder, 'mt.id');
    }
}
