<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status\Grid;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class ComplaintStatusGridFactory implements GridFactoryInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @param string|null $editRole
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    #[Override]
    public function create(?string $editRole): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('cs, cst')
            ->from(ComplaintStatus::class, 'cs')
            ->join('cs.translations', 'cst', Join::WITH, 'cst.locale = :locale')
            ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'cs.id');

        $grid = $this->gridFactory->create('complaintStatusList', $dataSource, $editRole);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'cst.name', t('Name'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_complaintstatus_deleteconfirm', ['id' => 'cs.id'])
            ?->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/ComplaintStatus/listGrid.html.twig', [
            'STATUS_TYPE_NEW' => ComplaintStatusTypeEnum::STATUS_TYPE_NEW,
            'STATUS_TYPE_RESOLVED' => ComplaintStatusTypeEnum::STATUS_TYPE_RESOLVED,
        ]);

        return $grid;
    }
}
