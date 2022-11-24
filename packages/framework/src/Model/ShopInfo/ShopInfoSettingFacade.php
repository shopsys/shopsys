<?php

namespace Shopsys\FrameworkBundle\Model\ShopInfo;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class ShopInfoSettingFacade
{
    public const SHOP_INFO_PHONE_NUMBER = 'shopInfoPhoneNumber';
    public const SHOP_INFO_EMAIL = 'shopInfoEmail';
    public const SHOP_INFO_PHONE_HOURS = 'shopInfoPhoneHours';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getPhoneNumber(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SHOP_INFO_PHONE_NUMBER, $domainId);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getEmail(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SHOP_INFO_EMAIL, $domainId);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getPhoneHours(int $domainId): ?string
    {
        return $this->setting->getForDomain(self::SHOP_INFO_PHONE_HOURS, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setPhoneNumber(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SHOP_INFO_PHONE_NUMBER, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setEmail(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SHOP_INFO_EMAIL, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setPhoneHours(?string $value, int $domainId): void
    {
        $this->setting->setForDomain(self::SHOP_INFO_PHONE_HOURS, $value, $domainId);
    }
}
