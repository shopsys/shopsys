<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Component\Setting\Setting;

class StockSettingsDataFactory
{
    /**
     * @var \App\Component\Setting\Setting
     */
    private $setting;

    /**
     * @param \App\Component\Setting\Setting $setting
     */
    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
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
