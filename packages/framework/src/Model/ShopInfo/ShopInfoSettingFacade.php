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

    /**
     * @param int $domainId
     */
    public function getPhoneNumber($domainId): ?string
    {
        return $this->setting->getForDomain(self::SHOP_INFO_PHONE_NUMBER, $domainId);
    }

    /**
     * @param int $domainId
     */
    public function getEmail($domainId): ?string
    {
        return $this->setting->getForDomain(self::SHOP_INFO_EMAIL, $domainId);
    }

    /**
     * @param int $domainId
     */
    public function getPhoneHours($domainId): ?string
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
