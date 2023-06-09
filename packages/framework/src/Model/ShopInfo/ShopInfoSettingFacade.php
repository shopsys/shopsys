<?php

namespace Shopsys\FrameworkBundle\Model\ShopInfo;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class ShopInfoSettingFacade
{
    public const SHOP_INFO_PHONE_NUMBER = 'shopInfoPhoneNumber';
    public const SHOP_INFO_EMAIL = 'shopInfoEmail';
    public const SHOP_INFO_PHONE_HOURS = 'shopInfoPhoneHours';

    protected Setting $setting;

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
    public function getPhoneNumber($domainId)
    {
        return $this->setting->getForDomain(self::SHOP_INFO_PHONE_NUMBER, $domainId);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getEmail($domainId)
    {
        return $this->setting->getForDomain(self::SHOP_INFO_EMAIL, $domainId);
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    public function getPhoneHours($domainId)
    {
        return $this->setting->getForDomain(self::SHOP_INFO_PHONE_HOURS, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setPhoneNumber($value, $domainId)
    {
        $this->setting->setForDomain(self::SHOP_INFO_PHONE_NUMBER, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setEmail($value, $domainId)
    {
        $this->setting->setForDomain(self::SHOP_INFO_EMAIL, $value, $domainId);
    }

    /**
     * @param string|null $value
     * @param int $domainId
     */
    public function setPhoneHours($value, $domainId)
    {
        $this->setting->setForDomain(self::SHOP_INFO_PHONE_HOURS, $value, $domainId);
    }
}
