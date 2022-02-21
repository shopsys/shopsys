<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\ShopInfo\ShopInfoSettingFacade;

class SettingValueShopInfoDataFixture extends AbstractReferenceFixture
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    public const SETTING_VALUES = [
        ShopInfoSettingFacade::SHOP_INFO_PHONE_NUMBER => '+1-234-567-8989',
        ShopInfoSettingFacade::SHOP_INFO_PHONE_HOURS => '(Mon - Sat: 9 - 10 a.m. to 8 - 10 p.m.)',
        ShopInfoSettingFacade::SHOP_INFO_EMAIL => 'no-reply@shopsys.com',
    ];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::SETTING_VALUES as $key => $value) {
            $this->setting->setForDomain($key, $value, Domain::FIRST_DOMAIN_ID);
        }
    }
}
