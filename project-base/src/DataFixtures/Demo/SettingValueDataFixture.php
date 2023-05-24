<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;

class SettingValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
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

            /** @var \App\Model\Article\Article $termsAndConditions */
            $termsAndConditions = $this->getReferenceForDomain(
                ArticleDataFixture::ARTICLE_TERMS_AND_CONDITIONS,
                $domainId
            );
            $this->setting->setForDomain(
                Setting::TERMS_AND_CONDITIONS_ARTICLE_ID,
                $termsAndConditions->getId(),
                $domainId
            );

            /** @var \App\Model\Article\Article $privacyPolicy */
            $privacyPolicy = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_PRIVACY_POLICY, $domainId);
            $this->setting->setForDomain(Setting::PRIVACY_POLICY_ARTICLE_ID, $privacyPolicy->getId(), $domainId);

            /** @var \App\Model\Article\Article $cookies */
            $cookies = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_COOKIES, $domainId);
            $this->setting->setForDomain(Setting::COOKIES_ARTICLE_ID, $cookies->getId(), $domainId);

            $personalDataDisplaySiteContent = t(
                'By entering an email below, you can view your personal information that we register in our online store.
                An email with a link will be sent to you after entering your email address, to verify your identity.
                Clicking on the link will take you to a page listing all the personal details we have connected to your email address.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale
            );
            $this->setting->setForDomain(
                Setting::PERSONAL_DATA_DISPLAY_SITE_CONTENT,
                $personalDataDisplaySiteContent,
                $domainId
            );

            $personalDataExportSiteContent = t(
                'By entering an email below, you can download your personal and other information (for example, order history)
                from our online store. An email with a link will be sent to you after entering your email address, to verify your identity.
                Clicking on the link will take you to a page where you’ll be able to download these informations in readable format - it will be the data
                registered to given email address on this online store domain.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale
            );
            $this->setting->setForDomain(
                Setting::PERSONAL_DATA_EXPORT_SITE_CONTENT,
                $personalDataExportSiteContent,
                $domainId
            );

            $orderSentText = t('
                <p>
                    Order number {number} has been sent, thank you for your purchase.
                    We will contact you about next order status. <br /><br />
                    <a href="{order_detail_url}">Track</a> the status of your order. <br />
                    {transport_instructions} <br />
                    {payment_instructions} <br />
                </p>
            ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $this->setting->setForDomain(Setting::ORDER_SENT_PAGE_CONTENT, $orderSentText, $domainId);

            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
            $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, $domainId);
            $this->setting->setForDomain(Setting::DEFAULT_PRICING_GROUP, $pricingGroup->getId(), $domainId);

            $this->setting->setForDomain(
                SeoSettingFacade::SEO_META_DESCRIPTION_MAIN_PAGE,
                t('Shopsys Framework - the best solution for your eshop.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId
            );
            $this->setting->setForDomain(
                SeoSettingFacade::SEO_TITLE_MAIN_PAGE,
                t('Shopsys Framework - Title page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId
            );
            $this->setting->setForDomain(
                SeoSettingFacade::SEO_TITLE_ADD_ON,
                t('| Demo eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId
            );
            $this->setting->setForDomain(
                SeoSettingFacade::SEO_ROBOTS_TXT_CONTENT,
                'Disallow: /admin',
                $domainId
            );
            $this->setting->setForDomain(
                MailSetting::MAIL_WHITELIST,
                '["/@shopsys\\\\.com$/"]',
                $domainId
            );

            $this->setDomainDefaultCurrency($domainId);
        }
    }

    /**
     * @param int $domainId
     */
    private function setDomainDefaultCurrency(int $domainId): void
    {
        if ($domainId === Domain::SECOND_DOMAIN_ID) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $defaultCurrency */
            $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
        } else {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $defaultCurrency */
            $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
        }
        $this->setting->setForDomain(PricingSetting::DEFAULT_DOMAIN_CURRENCY, $defaultCurrency->getId(), $domainId);
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
