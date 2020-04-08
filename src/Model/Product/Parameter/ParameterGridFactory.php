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
        $grid = parent::create();

        foreach ($grid->getActionColumns() as $actionColumn) {
            if ($actionColumn->getType() === ActionColumn::TYPE_DELETE) {
                $actionColumn->setConfirmMessage(t(
                    'Opravdu chcete odstranit tento parametr?'
                    . ' Smazáním parametru dojde k odstranění tohoto parametru u zboží a případné landing stránky'
                    . ' rozšířeného SEO kategorií, kde je parametr přiřazen. Tento krok je nevratný!'
                ));
            }
        }

        return $grid;
    }
}
