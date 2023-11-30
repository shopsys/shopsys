<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class StockSettingsDataFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockSettingsData $stockSettingsData
     */
    public function edit(StockSettingsData $stockSettingsData): void
    {
        $this->setting->setForDomain(Setting::DELIVERY_DAYS_ON_STOCK, (int)$stockSettingsData->delivery, $this->adminDomainTabsFacade->getSelectedDomainId());
        $this->setting->setForDomain(Setting::TRANSFER_DAYS_BETWEEN_STOCKS, (int)$stockSettingsData->transfer, $this->adminDomainTabsFacade->getSelectedDomainId());
    }
}
