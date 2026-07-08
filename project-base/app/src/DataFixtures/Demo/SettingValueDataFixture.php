<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Component\Setting\Setting;
use App\Model\Article\Article;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationLocaleHelper;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class SettingValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const int FREE_TRANSPORT_AND_PAYMENT_LIMIT = 50000;

    public function __construct(
        private readonly Setting $setting,
        private readonly HeurekaShopCertificationLocaleHelper $heurekaShopCertificationLocaleHelper,
        private readonly Domain $domain,
        private readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
        private readonly CurrencyFacade $currencyFacade,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            if ($domainId === 1) {
                $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
                $freeTransportLimitsByCurrencyCode = [];

                foreach ($domainConfig->getCurrencyCodes() as $currencyCode) {
                    $targetCurrency = $this->currencyFacade->getByCode($currencyCode);
                    $convertedLimit = Money::create(self::FREE_TRANSPORT_AND_PAYMENT_LIMIT)->multiply(
                        (string)$this->currencyFacade->getExchangeRateForCurrencies($currencyCzk, $targetCurrency),
                    );
                    $freeTransportLimitsByCurrencyCode[$currencyCode] = $convertedLimit;
                }

                $this->freeTransportAndPaymentFacade->setPriceLimits($domainId, $freeTransportLimitsByCurrencyCode);
            }

            $termsAndConditions = $this->getReferenceForDomain(
                ArticleDataFixture::ARTICLE_TERMS_AND_CONDITIONS,
                $domainId,
                Article::class,
            );
            $this->setting->setForDomain(
                Setting::TERMS_AND_CONDITIONS_ARTICLE_ID,
                $termsAndConditions->getId(),
                $domainId,
            );

            $privacyPolicy = $this->getReferenceForDomain(ArticleDataFixture::ARTICLE_PRIVACY_POLICY, $domainId, Article::class);
            $this->setting->setForDomain(Setting::PRIVACY_POLICY_ARTICLE_ID, $privacyPolicy->getId(), $domainId);

            $userConsentPolicyArticle = $this->getReferenceForDomain(ArticleDataFixture::USER_CONSENT_POLICY_ARTICLE, $domainId, Article::class);
            $this->setting->setForDomain(Setting::USER_CONSENT_POLICY_ARTICLE_ID, $userConsentPolicyArticle->getId(), $domainId);

            $personalDataDisplaySiteContent = t(
                'By entering an email below, you can view your personal information that we register in our online store.
                An email with a link will be sent to you after entering your email address, to verify your identity.
                Clicking on the link will take you to a page listing all the personal details we have connected to your email address.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
            $this->setting->setForDomain(
                Setting::PERSONAL_DATA_DISPLAY_SITE_CONTENT,
                $personalDataDisplaySiteContent,
                $domainId,
            );

            $personalDataExportSiteContent = t(
                'By entering an email below, you can download your personal and other information (for example, order history)
                from our online store. An email with a link will be sent to you after entering your email address, to verify your identity.
                Clicking on the link will take you to a page where you’ll be able to download these informations in readable format - it will be the data
                registered to given email address on this online store domain.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
            $this->setting->setForDomain(
                Setting::PERSONAL_DATA_EXPORT_SITE_CONTENT,
                $personalDataExportSiteContent,
                $domainId,
            );

            $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, $domainId, PricingGroup::class);
            $this->setting->setForDomain(Setting::DEFAULT_PRICING_GROUP, $pricingGroup->getId(), $domainId);

            $this->setting->setForDomain(
                SeoSettingFacade::SEO_META_DESCRIPTION_MAIN_PAGE,
                t('Shopsys Platform - the best solution for your eshop.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId,
            );
            $this->setting->setForDomain(
                SeoSettingFacade::SEO_TITLE_MAIN_PAGE,
                t('Shopsys Platform - Title page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId,
            );
            $this->setting->setForDomain(
                SeoSettingFacade::SEO_TITLE_ADD_ON,
                t('| Demo eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                $domainId,
            );
            $this->setting->setForDomain(
                Setting::TRANSFER_DAYS_BETWEEN_STOCKS,
                7,
                $domainId,
            );

            $this->setting->setForDomain(
                MailSetting::MAIL_WHITELIST,
                '["/@shopsys\\\\.com$/"]',
                $domainId,
            );

            if ($this->heurekaShopCertificationLocaleHelper->isDomainLocaleSupported($locale)) {
                $this->setting->setForDomain(
                    HeurekaSetting::HEUREKA_API_KEY,
                    'JustMadeUpDemoKeyToEnableHeureka',
                    $domainId,
                );
            }
        }
        $this->setting->set(BaseSetting::FILE_STRUCTURE_MIGRATED_FOR_RELATIONS, true);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            ArticleDataFixture::class,
            PricingGroupDataFixture::class,
            CurrencyDataFixture::class,
        ];
    }
}
