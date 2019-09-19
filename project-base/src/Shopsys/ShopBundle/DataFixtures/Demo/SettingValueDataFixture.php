<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class SettingValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
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
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var \Shopsys\ShopBundle\Model\Article\Article $termsAndConditions */
        $termsAndConditions = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_TERMS_AND_CONDITIONS, 1);
        /** @var \Shopsys\ShopBundle\Model\Article\Article $privacyPolicy */
        $privacyPolicy = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_PRIVACY_POLICY, 1);
        /** @var \Shopsys\ShopBundle\Model\Article\Article $cookies */
        $cookies = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_COOKIES, 1);

        $personalDataDisplaySiteContent = 'By entering an email below, you can view your personal information that we register in our online store. 
        An email with a link will be sent to you after entering your email address, to verify your identity. 
        Clicking on the link will take you to a page listing all the personal details we have connected to your email address.';

        $personalDataExportSiteContent = 'By entering an email below, you can download your personal and other information (for example, order history)
         from our online store. An email with a link will be sent to you after entering your email address, to verify your identity. 
         Clicking on the link will take you to a page where you’ll be able to download these informations in readable format - it will be the data 
         registered to given email address on this online store domain.';

        $this->setting->setForDomain(Setting::COOKIES_ARTICLE_ID, $cookies->getId(), Domain::FIRST_DOMAIN_ID);
        $this->setting->setForDomain(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $termsAndConditions->getId(), Domain::FIRST_DOMAIN_ID);
        $this->setting->setForDomain(Setting::PRIVACY_POLICY_ARTICLE_ID, $privacyPolicy->getId(), Domain::FIRST_DOMAIN_ID);
        $this->setting->setForDomain(Setting::PERSONAL_DATA_DISPLAY_SITE_CONTENT, $personalDataDisplaySiteContent, Domain::FIRST_DOMAIN_ID);
        $this->setting->setForDomain(Setting::PERSONAL_DATA_EXPORT_SITE_CONTENT, $personalDataExportSiteContent, Domain::FIRST_DOMAIN_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            ArticleDataFixture::class,
            PricingGroupDataFixture::class,
            CurrencyDataFixture::class,
        ];
    }
}
