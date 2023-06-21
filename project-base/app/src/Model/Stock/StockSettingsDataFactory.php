<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Component\Setting\Setting;

class StockSettingsDataFactory
{
    /**
     * @param \App\Component\Setting\Setting $setting
     */
    public function __construct(private Setting $setting)
    {
    }

    /**
     * @param int $domainId
     * @return \App\Model\Stock\StockSettingsData
     */
    public function getForDomainId(int $domainId): StockSettingsData
    {
        $settings = new StockSettingsData();
        $settings->delivery = $this->setting->getForDomain(Setting::DELIVERY_DAYS_ON_STOCK, $domainId);
        $settings->transfer = $this->setting->getForDomain(Setting::TRANSFER_DAYS_BETWEEN_STOCKS, $domainId);

        return $settings;
    }
}
