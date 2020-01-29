<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class StockSettingsDataFacade implements StockSettingsDataFacadeInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(Setting $setting, AdminDomainTabsFacade $adminDomainTabsFacade)
    {
        $this->setting = $setting;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @param \App\Model\Stock\StockSettingsData $stockSettingsData
     */
    public function edit(StockSettingsData $stockSettingsData): void
    {
        $this->setting->setForDomain(StockSettingsData::DELIVERY_DAYS_ON_STOCK, (int)$stockSettingsData->delivery, $this->adminDomainTabsFacade->getSelectedDomainId());
        $this->setting->setForDomain(StockSettingsData::TRANSFER_DAYS_BETWEEN_STOCKS, (int)$stockSettingsData->transfer, $this->adminDomainTabsFacade->getSelectedDomainId());
    }
}
