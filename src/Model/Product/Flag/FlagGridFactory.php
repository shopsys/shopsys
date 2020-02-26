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
            ->select('a, at')
            ->from(Flag::class, 'a')
            ->join('a.translations', 'at', Join::WITH, 'at.locale = :locale')
            ->setParameter('locale', $this->localization->getAdminLocale());
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'a.id');

        $grid = $this->gridFactory->create('flagList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'at.name', t('Name'), true);
        $grid->addColumn('rgbColor', 'a.rgbColor', t('Color'), true);
        $grid->addColumn('visible', 'a.visible', t('Display'), true);
        $grid->addColumn('sale', 'a.sale', t('Označení výprodeje'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');

        $grid->setTheme('@ShopsysFramework/Admin/Content/Flag/listGrid.html.twig');

        return $grid;
    }
}
