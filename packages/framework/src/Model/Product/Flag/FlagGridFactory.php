<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
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
        $dataSource = new QueryBuilderWithRowManipulatorDataSource(
            $queryBuilder,
            'f.id',
            function ($row) {
                $color = strtr($row['f']['rgbColor'], ['#' => '']);
                $r = hexdec(substr($color, 0, 2));
                $g = hexdec(substr($color, 2, 2));
                $b = hexdec(substr($color, 4, 2));

                $brightness = ($r * 0.299 + $g * 0.587 + $b * 0.114);
                $row['textColor'] = $brightness > 128 ? '#25283d' : '#ffffff';

                return $row;
            },
        );

        $grid = $this->gridFactory->create('flagList', $dataSource);
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
}
