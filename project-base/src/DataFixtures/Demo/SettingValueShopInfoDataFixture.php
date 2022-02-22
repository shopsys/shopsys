<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

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
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(Setting $setting, Domain $domain)
    {
        $this->setting = $setting;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();
            $this->setting->setForDomain(
                ShopInfoSettingFacade::SHOP_INFO_PHONE_NUMBER,
                t('+1-234-567-8989', [], 'dataFixtures', $locale),
                $domainId
            );
            $this->setting->setForDomain(
                ShopInfoSettingFacade::SHOP_INFO_PHONE_HOURS,
                t('(Mon - Sat: 9 - 10 a.m. to 8 - 10 p.m.)', [], 'dataFixtures', $locale),
                $domainId
            );
            $this->setting->setForDomain(
                ShopInfoSettingFacade::SHOP_INFO_EMAIL,
                t('no-reply@shopsys.com', [], 'dataFixtures', $locale),
                $domainId
            );
        }
    }
}
