<?php

declare(strict_types=1);

namespace App\Model\Stock;

interface StockSettingsDataFactoryInterface
{
    /**
     * @return \App\Model\Stock\StockSettingsData
     */
    public function create(): StockSettingsData;
}
