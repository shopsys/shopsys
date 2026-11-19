<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class ComplaintStatusGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
        protected readonly ComplaintStatusTypeEnum $complaintStatusTypeEnum,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('cs, cst')
            ->from(ComplaintStatus::class, 'cs')
            ->join('cs.translations', 'cst', Join::WITH, 'cst.locale = :locale')
            ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities());
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'cs.id');

        $grid = $this->gridFactory->create('complaintStatusList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'cst.name', t('Name'), true);
        $grid->addColumn('statusType', 'cs.statusType', t('Type'), true, [
            'help' => t('Products of complaints in the statuses "In Progress" and "Resolved, the products cannot be complained about again" (e.g. money return) stay claimed. Products of complaints in the status "Resolved, the products can be complained about again" (e.g. rejection or fix) can be claimed in new complaints.'),
        ]);

        $grid->addDeleteActionColumn('admin_complaintstatus_deleteconfirm', ['id' => 'cs.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAdministration/content/complaintStatus/listGrid.html.twig', [
            'complaintStatusTypeEnum' => $this->complaintStatusTypeEnum,
        ]);

        return $grid;
    }
}
