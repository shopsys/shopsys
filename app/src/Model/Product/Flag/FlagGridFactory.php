<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagGridFactory as BaseFlagGridFactory;

class FlagGridFactory extends BaseFlagGridFactory
{
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
            ->setParameter('locale', $this->localization->getAdminLocale());
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
