<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Tests\App\Test\Codeception\Module\StrictWebDriver;

class LocalizationHelper extends Module
{
    private AdministratorLocalizationFacade $administratorLocalizationFacade;

    private DomainRouterFactory $domainRouterFactory;

    private StrictWebDriver $webDriver;

    private UnitFacade $unitFacade;

    #[Override]
    public function _before(TestInterface $test): void
    {
        /** @var \Tests\App\Test\Codeception\Helper\SymfonyHelper $symfonyHelper */
        $symfonyHelper = $this->getModule(SymfonyHelper::class);
        /** @var \Tests\App\Test\Codeception\Module\StrictWebDriver $strictWebDriver */
        $strictWebDriver = $this->getModule(StrictWebDriver::class);
        $this->webDriver = $strictWebDriver;
        $this->administratorLocalizationFacade = $symfonyHelper->grabServiceFromContainer(AdministratorLocalizationFacade::class);
        $this->domainRouterFactory = $symfonyHelper->grabServiceFromContainer(DomainRouterFactory::class);
        $this->unitFacade = $symfonyHelper->grabServiceFromContainer(UnitFacade::class);
    }

    public function seeTranslationAdmin(
        string $id,
        string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN,
        array $parameters = [],
    ): void {
        $translatedMessage = t($id, $parameters, $translationDomain, $this->getAdminLocale());
        $this->webDriver->see(strip_tags($translatedMessage));
    }

    public function seeTranslationAdminInCss(
        string $id,
        string $css,
        string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN,
        array $parameters = [],
    ): void {
        $translatedMessage = t($id, $parameters, $translationDomain, $this->getAdminLocale());
        $this->webDriver->seeInCss(strip_tags($translatedMessage), $css);
    }

    public function clickByTranslationAdmin(
        string $id,
        string $translationDomain = Translator::DEFAULT_TRANSLATION_DOMAIN,
        array $parameters = [],
        WebDriverBy|WebDriverElement|null $contextSelector = null,
    ): void {
        $translatedMessage = t($id, $parameters, $translationDomain, $this->getAdminLocale());
        $this->webDriver->clickByText(strip_tags($translatedMessage), $contextSelector);
    }

    public function getAdminLocale(): string
    {
        return $this->administratorLocalizationFacade->getCurrentAdminLocaleOrDefault();
    }

    private function getLocalizedPathOnFirstDomainByRouteName(string $routeName, array $parameters = []): string
    {
        $router = $this->domainRouterFactory->getRouter(Domain::FIRST_DOMAIN_ID);

        return $router->generate($routeName, $parameters);
    }

    public function amOnLocalizedRoute(string $routeName, array $parameters = []): void
    {
        $this->webDriver->amOnPage($this->getLocalizedPathOnFirstDomainByRouteName($routeName, $parameters));
    }

    public function getDefaultUnitName(): string
    {
        return $this->unitFacade->getDefaultUnit()->getName($this->getAdminLocale());
    }
}
