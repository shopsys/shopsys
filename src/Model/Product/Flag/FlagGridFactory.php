<?php

declare(strict_types=1);

namespace App\Model\Product\Flag;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagGridFactory as BaseFlagGridFactory;

class FlagGridFactory extends BaseFlagGridFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $grid = parent::create();
        $grid->addColumn('sale', 'a.sale', t('Označení výprodeje'), true);

        return $grid;
    }
}
