<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class FlagGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
        protected readonly QueryBuilderWithRowManipulatorDataSourceFactory $queryBuilderWithRowManipulatorDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('f, ft')
            ->from(Flag::class, 'f')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities());
        $dataSource = $this->queryBuilderWithRowManipulatorDataSourceFactory->create(
            $queryBuilder,
            'f.id',
            $this->addTextColorToEnsureReadability(...),
        );

        $grid = $this->gridFactory->create('flagList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'ft.name', t('Name'), true);
        $grid->addColumn('rgbColor', 'f.rgbColor', t('Color'), true);
        $grid->addColumn('visible', 'f.visible', t('Display'), true);

        $grid->addEditActionColumn('admin_flag_edit', ['id' => 'f.id']);
        $grid->addDeleteActionColumn('admin_flag_deleteconfirm', ['id' => 'f.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAdministration/content/flag/listGrid.html.twig');

        return $grid;
    }

    protected function addTextColorToEnsureReadability(array $row): array
    {
        $color = strtr($row['f']['rgbColor'], ['#' => '']);
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));

        $brightness = ($r * 0.299 + $g * 0.587 + $b * 0.114);
        $row['textColor'] = $brightness > 128 ? '#25283d' : '#ffffff';

        return $row;
    }
}
