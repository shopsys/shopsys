<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use App\Component\Setting\Setting;

class StockSettingsDataFactory implements StockSettingsDataFactoryInterface
{
    /**
     * @var \App\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(Setting $setting, AdminDomainTabsFacade $adminDomainTabsFacade)
    {
        $this->setting = $setting;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @return \App\Model\Stock\StockSettingsData
     */
    public function create(): StockSettingsData
    {
        $settings = new StockSettingsData();
        $settings->delivery = $this->setting->getForDomain(Setting::DELIVERY_DAYS_ON_STOCK, $this->adminDomainTabsFacade->getSelectedDomainId());
        $settings->transfer = $this->setting->getForDomain(Setting::TRANSFER_DAYS_BETWEEN_STOCKS, $this->adminDomainTabsFacade->getSelectedDomainId());

        return $settings;
    }
}
