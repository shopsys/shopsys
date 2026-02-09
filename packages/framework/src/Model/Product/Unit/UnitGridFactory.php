<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class UnitGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('u, ut')
            ->from(Unit::class, 'u')
            ->join('u.translations', 'ut', Join::WITH, 'ut.locale = :locale')
            ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities());
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'u.id');

        $grid = $this->gridFactory->create('unitList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'ut.name', t('Name'), true);

        $grid->addDeleteActionColumn('admin_unit_deleteconfirm', ['id' => 'u.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAdministration/content/unit/listGrid.html.twig');

        return $grid;
    }
}
