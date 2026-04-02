<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use DateTimeImmutable;
use Override;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RequiredSettingExtension extends AbstractExtension
{
    /**
     * @var string[]
     */
    protected array $requiredSettingsMessages = [];

    protected const int DAYS_BEFORE_YEAR_END_TO_WARN_ABOUT_HOLIDAYS = 30;

    public function __construct(
        protected readonly Environment $twig,
        protected readonly RouterInterface $router,
        protected readonly Domain $domain,
        protected readonly Setting $setting,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly StockFacade $stockFacade,
        protected readonly CountryFacade $countryFacade,
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly ClockInterface $clock,
        protected readonly SeoSettingFacade $seoSettingFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_required_settings', $this->renderRequiredSettings(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderRequiredSettings(): ?string
    {
        $this->loadRequiredSettingMessages();

        if (count($this->requiredSettingsMessages) === 0) {
            return null;
        }

        return $this->twig->render(
            '@ShopsysAdministration/partial/required_settings.html.twig',
            [
                'requiredSettingsMessages' => $this->requiredSettingsMessages,
            ],
        );
    }

    protected function loadRequiredSettingMessages(): void
    {
        $this->requiredSettingsMessages = [];

        $this->checkEnabledMailTemplatesHaveTheirBodyAndSubjectFilled();
        $this->checkAtLeastOneStockExists();
        $this->checkAtLeastOneCountryExists();
        $this->checkMandatoryArticlesExist();
        $this->checkAllSliderNumericValuesAreSet();
        $this->checkPublicHolidaysAreSet();
        $this->checkSeoInformationIsSet();
    }

    protected function checkEnabledMailTemplatesHaveTheirBodyAndSubjectFilled(): void
    {
        if ($this->mailTemplateFacade->existsTemplateWithEnabledSendingHavingEmptyBodyOrSubject()) {
            $this->requiredSettingsMessages[] = t(
                '<a href="%url%">Some required email templates are not fully set.</a>',
                [
                    '%url%' => $this->router->generate('admin_mail_template'),
                ],
            );
        }
    }

    protected function checkAtLeastOneStockExists(): void
    {
        if ($this->stockFacade->getCount() === 0) {
            $this->requiredSettingsMessages[] = t(
                '<a href="%url%">There are no warehouses, you need to create some.</a>',
                [
                    '%url%' => $this->router->generate('admin_stock_list'),
                ],
            );
        }
    }

    protected function checkAtLeastOneCountryExists(): void
    {
        if ($this->countryFacade->getCount() === 0) {
            $this->requiredSettingsMessages[] = t(
                '<a href="%url%">There are no countries, you need to create some.</a>',
                [
                    '%url%' => $this->router->generate('admin_country_list'),
                ],
            );
        }
    }

    protected function checkMandatoryArticlesExist(): void
    {
        foreach ($this->domain->getAdminEnabledDomainIds() as $domainId) {
            $domainConfig = $this->domain->getDomainConfigById($domainId);

            if ($this->setting->getForDomain(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $domainConfig->getId()) === null) {
                $this->requiredSettingsMessages[] = t(
                    '<a href="%url%">Term and conditions article for domain %domainName% is not set.</a>',
                    [
                        '%url%' => $this->generateUrlWithSelectedDomainTab('admin_legalconditions_termsandconditions', $domainId),
                        '%domainName%' => $domainConfig->getName(),
                    ],
                );
            }

            if ($this->setting->getForDomain(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainConfig->getId()) === null) {
                $this->requiredSettingsMessages[] = t(
                    '<a href="%url%">Privacy policy article for domain %domainName% is not set.</a>',
                    [
                        '%url%' => $this->generateUrlWithSelectedDomainTab('admin_legalconditions_privacypolicy', $domainId),
                        '%domainName%' => $domainConfig->getName(),
                    ],
                );
            }

            if ($this->setting->getForDomain(Setting::USER_CONSENT_POLICY_ARTICLE_ID, $domainConfig->getId()) === null) {
                $this->requiredSettingsMessages[] = t(
                    '<a href="%url%">User consent policy article for domain %domainName% is not set.</a>',
                    [
                        '%url%' => $this->generateUrlWithSelectedDomainTab('admin_userconsentpolicy_setting', $domainId),
                        '%domainName%' => $domainConfig->getName(),
                    ],
                );
            }
        }
    }

    protected function generateUrlWithSelectedDomainTab(string $routeName, int $domainId): string
    {
        return $this->router->generate(
            $routeName,
            [
                AdminDomainTabsFacade::QUERY_PARAMETER_NAME => $domainId,
            ],
        );
    }

    protected function checkAllSliderNumericValuesAreSet(): void
    {
        $countOfSliderParametersWithoutNumericValueSet = $this->parameterFacade->getCountOfSliderParametersWithoutTheirsNumericValueFilled();

        if ($countOfSliderParametersWithoutNumericValueSet <= 0) {
            return;
        }

        $message = t(
            '{1} There is one parameter slider that does not have its numeric values filled in.|[2,Inf] There are %count% parameter sliders that does not have its numeric values filled in.',
            [
                '%count%' => $countOfSliderParametersWithoutNumericValueSet,
            ],
        );

        $sliderParametersWithoutTheirsNumericValueFilled = $this->parameterFacade->getSliderParametersWithoutTheirsNumericValueFilled();

        $message .= '<ul>';

        foreach ($sliderParametersWithoutTheirsNumericValueFilled as $parameter) {
            $message .= sprintf(
                '<li><a href="%s">%s</a></li>',
                $this->router->generate('admin_parametervalues_edit', ['id' => $parameter->getId()]),
                $parameter->getName(),
            );
        }
        $message .= '</ul>';

        $this->requiredSettingsMessages[] = $message;
    }

    protected function checkPublicHolidaysAreSet(): void
    {
        $now = $this->clock->now();
        $currentYear = (int)$now->format('Y');

        $yearsToCheck = [$currentYear];

        $yearEnd = new DateTimeImmutable($currentYear . '-12-31');
        $daysUntilYearEnd = $now->diff($yearEnd)->days ?: 0;

        if ($daysUntilYearEnd <= static::DAYS_BEFORE_YEAR_END_TO_WARN_ABOUT_HOLIDAYS) {
            $yearsToCheck[] = $currentYear + 1;
        }

        foreach ($yearsToCheck as $year) {
            $this->checkPublicHolidaysForYearAreSet($year);
        }
    }

    protected function checkPublicHolidaysForYearAreSet(int $year): void
    {
        $yearStart = new DateTimeImmutable($year . '-01-01');
        $yearEnd = new DateTimeImmutable($year . '-12-31');

        foreach ($this->domain->getAdminEnabledDomainIds() as $domainId) {
            if (!$this->closedDayFacade->hasPublicHolidays($domainId, $yearStart, $yearEnd)) {
                $this->requiredSettingsMessages[] = t(
                    '<a href="%url%">Public holidays for year %year% are not set.</a>',
                    [
                        '%url%' => $this->router->generate('admin_closedday_list'),
                        '%year%' => $year,
                    ],
                );

                return;
            }
        }
    }

    protected function checkSeoInformationIsSet(): void
    {
        foreach ($this->domain->getAdminEnabledDomainIds() as $domainId) {
            $titleMainPage = $this->seoSettingFacade->getTitleMainPage($domainId);
            $descriptionMainPage = $this->seoSettingFacade->getDescriptionMainPage($domainId);

            $isTitleMissing = $titleMainPage === null || $titleMainPage === '';
            $isDescriptionMissing = $descriptionMainPage === null || $descriptionMainPage === '';

            if (!$isTitleMissing && !$isDescriptionMissing) {
                continue;
            }

            $domainConfig = $this->domain->getDomainConfigById($domainId);

            $this->requiredSettingsMessages[] = t(
                '<a href="%url%">SEO information for main page for domain %domainName% is not fully set.</a>',
                [
                    '%url%' => $this->generateUrlWithSelectedDomainTab('admin_seo_index', $domainId),
                    '%domainName%' => $domainConfig->getName(),
                ],
            );
        }
    }
}
