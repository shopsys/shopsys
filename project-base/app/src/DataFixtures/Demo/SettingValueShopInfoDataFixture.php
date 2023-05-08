<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\ShopInfo\ShopInfoSettingFacade;

class SettingValueShopInfoDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(private readonly Setting $setting, private readonly Domain $domain)
    {
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
                t('+1-234-567-8989', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId,
            );
            $this->setting->setForDomain(
                ShopInfoSettingFacade::SHOP_INFO_PHONE_HOURS,
                t('(Mon - Sat: 9 - 10 a.m. to 8 - 10 p.m.)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId,
            );
            $this->setting->setForDomain(
                ShopInfoSettingFacade::SHOP_INFO_EMAIL,
                t('no-reply@shopsys.com', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId,
            );
        }
    }
}
