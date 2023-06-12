<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;

class StockSettingsDataFacade
{
    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        private Setting $setting,
        private AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @param \App\Model\Stock\StockSettingsData $stockSettingsData
     */
    public function edit(StockSettingsData $stockSettingsData): void
    {
        $this->setting->setForDomain(Setting::DELIVERY_DAYS_ON_STOCK, (int)$stockSettingsData->delivery, $this->adminDomainTabsFacade->getSelectedDomainId());
        $this->setting->setForDomain(Setting::TRANSFER_DAYS_BETWEEN_STOCKS, (int)$stockSettingsData->transfer, $this->adminDomainTabsFacade->getSelectedDomainId());
    }
}
