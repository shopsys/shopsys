<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\ActionColumn;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGridFactory as BaseParameterGridFactory;

class ParameterGridFactory extends BaseParameterGridFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create()
    {
        $locales = $this->localization->getLocalesOfAllDomains();
        $adminLocale = $this->localization->getAdminLocale();
        $grid = $this->gridFactory->create('parameterList', $this->getParametersDataSource());
        $grid->setDefaultOrder('pt.name');

        if (count($locales) > 1) {
            $grid->addColumn(
                'name',
                'pt.name',
                t('Name %locale%', ['%locale%' => $this->localization->getLanguageName($adminLocale)]),
                true,
            );

            foreach ($locales as $locale) {
                if ($locale !== $adminLocale) {
                    $grid->addColumn(
                        'name_' . $locale,
                        'pt_' . $locale . '.name',
                        t('Name %locale%', ['%locale%' => $this->localization->getLanguageName($locale)]),
                        true,
                    );
                }
            }
        } else {
            $grid->addColumn(
                'name',
                'pt.name',
                t('Name'),
                true,
            );
        }

        $grid->addColumn(
            'parameterType',
            'p.parameterType',
            t('Type'),
        );

        $grid->addColumn(
            'parameterGroup',
            'pgt.name',
            t('Group'),
        );

        $grid->addColumn(
            'parameterUnit',
            'ut.name',
            t('Unit'),
        );

        $grid->setActionColumnClassAttribute('table-col table-col-10');

        $grid->addEditActionColumn('admin_parameter_edit', ['id' => 'p.id']);

        $grid->addDeleteActionColumn('admin_parameter_delete', ['id' => 'p.id'])
            ->setConfirmMessage(t('Do you really want to remove this parameter? By deleting this parameter you will '
                . 'remove this parameter from a product where the parameter is assigned. This step is irreversible!'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Parameter/listGrid.html.twig');

        foreach ($grid->getActionColumns() as $actionColumn) {
            if ($actionColumn->getType() === ActionColumn::TYPE_DELETE) {
                $actionColumn->setConfirmMessage(t(
                    'Do you really want to remove this parameter?'
                    . ' Deleting the parameter will remove this parameter from the products and the possible landing page'
                    . ' of the extended SEO category where the parameter is assigned. This step is irreversible!',
                ));
            }
        }

        return $grid;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource
     */
    protected function getParametersDataSource()
    {
        $locales = $this->localization->getLocalesOfAllDomains();
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('p, pt, ut, pgt')
            ->from(Parameter::class, 'p')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->leftJoin('p.unit', 'u')
            ->leftJoin('u.translations', 'ut', Join::WITH, 'ut.locale = :locale')
            ->leftJoin('p.group', 'pg')
            ->leftJoin('pg.translations', 'pgt', Join::WITH, 'pgt.locale = :locale')
            ->setParameter('locale', $this->localization->getAdminLocale());

        foreach ($locales as $locale) {
            if ($locale !== $this->localization->getAdminLocale()) {
                $queryBuilder
                    ->addSelect('pt_' . $locale)
                    ->leftJoin(
                        'p.translations',
                        'pt_' . $locale,
                        Join::WITH,
                        'pt_' . $locale . '.locale = :locale_' . $locale,
                    )
                    ->setParameter('locale_' . $locale, $locale);
            }
        }

        return new QueryBuilderDataSource($queryBuilder, 'p.id');
    }
}
