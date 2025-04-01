<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class FlagGridFactory implements GridFactoryInterface
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
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create()
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('f, ft')
            ->from(Flag::class, 'f')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'f.id');

        $grid = $this->gridFactory->create('flagList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'ft.name', t('Name'), true);
        $grid->addColumn('rgbColor', 'f.rgbColor', t('Color'), true);
        $grid->addColumn('visible', 'f.visible', t('Display'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_flag_edit', ['id' => 'f.id']);
        $grid->addDeleteActionColumn('admin_flag_deleteconfirm', ['id' => 'f.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/Flag/listGrid.html.twig');

        return $grid;
    }
}
