<?php

namespace Shopsys\FrameworkBundle\Model\ShopInfo;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class ShopInfoSettingFacade
{
    const SHOP_INFO_PHONE_NUMBER = 'shopInfoPhoneNumber';
    const SHOP_INFO_EMAIL = 'shopInfoEmail';
    const SHOP_INFO_PHONE_HOURS = 'shopInfoPhoneHours';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    public function getPhoneNumber(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SHOP_INFO_PHONE_NUMBER, $domainId);
    }

    public function getEmail(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SHOP_INFO_EMAIL, $domainId);
    }

    public function getPhoneHours(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SHOP_INFO_PHONE_HOURS, $domainId);
    }

    public function setPhoneNumber(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SHOP_INFO_PHONE_NUMBER, $value, $domainId);
    }

    public function setEmail(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SHOP_INFO_EMAIL, $value, $domainId);
    }

    public function setPhoneHours(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SHOP_INFO_PHONE_HOURS, $value, $domainId);
    }
}
