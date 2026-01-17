<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class ParameterGridFactory implements GridFactoryInterface
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
        $locales = $this->localization->getLocalesOfAllDomains();
        $adminLocale = $this->localization->getCurrentLocaleForTranslatableEntities();
        $grid = $this->gridFactory->create('parameterList', $this->getParametersDataSource(), $roleConstant);

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

        $grid->addColumn('parameterType', 'p.parameterType', t('Type'));
        $grid->addColumn('parameterGroup', 'pgt.name', t('Group'));
        $grid->addColumn('parameterUnit', 'ut.name', t('Unit'));

        $grid->addEditActionColumn('admin_parameter_edit', ['id' => 'p.id']);


        $grid->addDeleteActionColumn('admin_parameter_delete', ['id' => 'p.id'])
            ->setConfirmMessage(t(
                'Do you really want to remove this parameter?'
                . ' Deleting the parameter will remove this parameter from the products and parameters'
                . ' of the extended SEO category where the parameter is assigned. This step is irreversible!',
            ));

        $grid->setTheme('@ShopsysAdministration/content/parameter/listGrid.html.twig');

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
            ->setParameter('locale', $this->localization->getCurrentLocaleForTranslatableEntities())
            ->orderBy('p.orderingPriority', 'DESC')
            ->addOrderBy('pt.name', 'ASC');

        foreach ($locales as $locale) {
            if ($locale !== $this->localization->getCurrentLocaleForTranslatableEntities()) {
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

        return $this->queryBuilderDataSourceFactory->create($queryBuilder, 'p.id');
    }
}
