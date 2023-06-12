<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\Grid\ActionColumn;
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
            t('Typ'),
        );

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addDeleteActionColumn('admin_parameter_delete', ['id' => 'p.id'])
            ->setConfirmMessage(t('Do you really want to remove this parameter? By deleting this parameter you will '
                . 'remove this parameter from a product where the parameter is assigned. This step is irreversible!'));

        $grid->setTheme('@ShopsysFramework/Admin/Content/Parameter/listGrid.html.twig');

        foreach ($grid->getActionColumns() as $actionColumn) {
            if ($actionColumn->getType() === ActionColumn::TYPE_DELETE) {
                $actionColumn->setConfirmMessage(t(
                    'Opravdu chcete odstranit tento parametr?'
                    . ' Smazáním parametru dojde k odstranění tohoto parametru u zboží a případné landing stránky'
                    . ' rozšířeného SEO kategorií, kde je parametr přiřazen. Tento krok je nevratný!',
                ));
            }
        }

        return $grid;
    }
}
