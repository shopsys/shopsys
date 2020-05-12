<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\Grid;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository;

class MailTemplateGridFactory implements GridFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    protected $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository
     */
    protected $mailTemplateRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration
     */
    protected $mailTemplateConfiguration;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateRepository $mailTemplateRepository
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration $mailTemplateConfiguration
     */
    public function __construct(
        MailTemplateRepository $mailTemplateRepository,
        GridFactory $gridFactory,
        AdminDomainTabsFacade $adminDomainTabsFacade,
        MailTemplateConfiguration $mailTemplateConfiguration
    ) {
        $this->gridFactory = $gridFactory;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->mailTemplateConfiguration = $mailTemplateConfiguration;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $grid = $this->gridFactory->create('MailTemplateList', $this->createDataSource());

        $grid->addColumn('name', 'mt.name', t('Name'), true);
        $grid->addColumn('subject', 'mt.subject', t('Subject'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_mail_edit', ['id' => 'mt.id']);

        $grid->setDefaultOrder('name');

        $grid->setTheme(
            '@ShopsysFramework/Admin/Content/Mail/listGrid.html.twig',
            [
                'readableNames' => $this->mailTemplateConfiguration->getReadableNamesIndexedBySlug(),
            ]
        );

        return $grid;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface
     */
    protected function createDataSource(): DataSourceInterface
    {
        $queryBuilder = $this->mailTemplateRepository->createQueryBuilder($this->adminDomainTabsFacade->getSelectedDomainId());

        return new QueryBuilderDataSource($queryBuilder, 'mt.id');
    }
}
