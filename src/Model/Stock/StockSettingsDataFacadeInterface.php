<?php

declare(strict_types=1);

namespace App\Model\Stock;

interface StockSettingsDataFacadeInterface
{
    /**
     * @param \App\Model\Stock\StockSettingsData $stockSettingsData
     */
    public function edit(StockSettingsData $stockSettingsData): void;
}
