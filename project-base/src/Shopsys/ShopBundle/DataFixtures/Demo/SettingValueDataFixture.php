<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;

class SettingValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

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
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            /** @var \Shopsys\ShopBundle\Model\Article\Article $termsAndConditions */
            $termsAndConditions = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_TERMS_AND_CONDITIONS, $domainId);
            $this->setting->setForDomain(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $termsAndConditions->getId(), $domainId);

            /** @var \Shopsys\ShopBundle\Model\Article\Article $privacyPolicy */
            $privacyPolicy = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_PRIVACY_POLICY, $domainId);
            $this->setting->setForDomain(Setting::PRIVACY_POLICY_ARTICLE_ID, $privacyPolicy->getId(), $domainId);

            /** @var \Shopsys\ShopBundle\Model\Article\Article $cookies */
            $cookies = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_COOKIES, $domainId);
            $this->setting->setForDomain(Setting::COOKIES_ARTICLE_ID, $cookies->getId(), $domainId);

            $personalDataDisplaySiteContent = t('By entering an email below, you can view your personal information that we register in our online store.
                An email with a link will be sent to you after entering your email address, to verify your identity.
                Clicking on the link will take you to a page listing all the personal details we have connected to your email address.', [], 'dataFixtures', $locale);
            $this->setting->setForDomain(Setting::PERSONAL_DATA_DISPLAY_SITE_CONTENT, $personalDataDisplaySiteContent, $domainId);

            $personalDataExportSiteContent = t('By entering an email below, you can download your personal and other information (for example, order history)
                from our online store. An email with a link will be sent to you after entering your email address, to verify your identity.
                Clicking on the link will take you to a page where you’ll be able to download these informations in readable format - it will be the data
                registered to given email address on this online store domain.', [], 'dataFixtures', $locale);
            $this->setting->setForDomain(Setting::PERSONAL_DATA_EXPORT_SITE_CONTENT, $personalDataExportSiteContent, $domainId);

            $orderSentText = t('
                <p>
                    Order number {number} has been sent, thank you for your purchase.
                    We will contact you about next order status. <br /><br />
                    <a href="{order_detail_url}">Track</a> the status of your order. <br />
                    {transport_instructions} <br />
                    {payment_instructions} <br />
                </p>
            ', [], 'dataFixtures', $locale);
            $this->setting->setForDomain(Setting::ORDER_SENT_PAGE_CONTENT, $orderSentText, $domainId);

            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup */
            $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, $domainId);
            $this->setting->setForDomain(Setting::DEFAULT_PRICING_GROUP, $pricingGroup->getId(), $domainId);

            $this->setting->setForDomain(
                SeoSettingFacade::SEO_META_DESCRIPTION_MAIN_PAGE,
                t('Shopsys Framework - the best solution for your eshop.', [], 'dataFixtures', $locale),
                $domainId
            );
            $this->setting->setForDomain(SeoSettingFacade::SEO_TITLE_MAIN_PAGE, t('Shopsys Framework - Title page', [], 'dataFixtures', $locale), $domainId);
            $this->setting->setForDomain(SeoSettingFacade::SEO_TITLE_ADD_ON, t('| Demo eshop', [], 'dataFixtures', $locale), $domainId);

            $this->setDomainDefaultCurrency($domainId);
        }
    }

    /**
     * @param int $domainId
     */
    protected function setDomainDefaultCurrency(int $domainId): void
    {
        if ($domainId === Domain::SECOND_DOMAIN_ID) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $defaultCurrency */
            $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_EUR);
        } else {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $defaultCurrency */
            $defaultCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
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
