<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class ParameterGridFactory implements GridFactoryInterface
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
        $locales = $this->localization->getLocalesOfAllDomains();
        $adminLocale = $this->localization->getAdminLocale();
        $grid = $this->gridFactory->create('parameterList', $this->getParametersDataSource());

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
        $grid->addColumn('parameterUnit', 'ut.name', t('Unit'));
        $grid->addColumn('visible', 'p.visible', t('Filter by'), true);

        $grid->addEditActionColumn('admin_parameter_edit', ['id' => 'p.id']);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_parameter_delete', ['id' => 'p.id'])
            ->setConfirmMessage(t('Do you really want to remove this parameter? By deleting this parameter you will '
                . 'remove this parameter from a product where the parameter is assigned. This step is irreversible!'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Parameter/listGrid.html.twig');

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
            ->select('p, pt, ut')
            ->from(Parameter::class, 'p')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->leftJoin('p.unit', 'u')
            ->leftJoin('u.translations', 'ut', Join::WITH, 'ut.locale = :locale')
            ->setParameter('locale', $this->localization->getAdminLocale())
            ->orderBy('p.orderingPriority', 'DESC')
            ->addOrderBy('pt.name', 'ASC');

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
