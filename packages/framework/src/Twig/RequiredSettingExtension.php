<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\Exception\UnitNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
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

    /**
     * @param \Twig\Environment $twig
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Model\Stock\StockFacade $stockFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     */
    public function __construct(
        protected readonly Environment $twig,
        protected readonly RouterInterface $router,
        protected readonly Domain $domain,
        protected readonly Setting $setting,
        protected readonly MailTemplateFacade $mailTemplateFacade,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly UnitFacade $unitFacade,
        protected readonly StockFacade $stockFacade,
        protected readonly CountryFacade $countryFacade,
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

    /**
     * @return string|null
     */
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
        $this->checkAtLeastOneUnitExists();
        $this->checkDefaultUnitIsSet();
        $this->checkAtLeastOneStockExists();
        $this->checkAtLeastOneCountryExists();
        $this->checkMandatoryArticlesExist();
        $this->checkAllSliderNumericValuesAreSet();
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

    protected function checkAtLeastOneUnitExists(): void
    {
        if ($this->unitFacade->getCount() === 0) {
            $this->requiredSettingsMessages[] = t(
                '<a href="%url%">There are no units, you need to create some.</a>',
                [
                    '%url%' => $this->router->generate('admin_unit_list'),
                ],
            );
        }
    }

    protected function checkDefaultUnitIsSet(): void
    {
        try {
            $this->unitFacade->getDefaultUnit();
        } catch (UnitNotFoundException) {
            $this->requiredSettingsMessages[] = t(
                '<a href="%url%">Default unit is not set.</a>',
                [
                    '%url%' => $this->router->generate('admin_unit_list'),
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
                        '%url%' => $this->router->generate('admin_legalconditions_termsandconditions'),
                        '%domainName%' => $domainConfig->getName(),
                    ],
                );
            }

            if ($this->setting->getForDomain(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainConfig->getId()) === null) {
                $this->requiredSettingsMessages[] = t(
                    '<a href="%url%">Privacy policy article for domain %domainName% is not set.</a>',
                    [
                        '%url%' => $this->router->generate('admin_legalconditions_privacypolicy'),
                        '%domainName%' => $domainConfig->getName(),
                    ],
                );
            }

            if ($this->setting->getForDomain(Setting::USER_CONSENT_POLICY_ARTICLE_ID, $domainConfig->getId()) === null) {
                $this->requiredSettingsMessages[] = t(
                    '<a href="%url%">User consent policy article for domain %domainName% is not set.</a>',
                    [
                        '%url%' => $this->router->generate('admin_userconsentpolicy_setting'),
                        '%domainName%' => $domainConfig->getName(),
                    ],
                );
            }
        }
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
}
