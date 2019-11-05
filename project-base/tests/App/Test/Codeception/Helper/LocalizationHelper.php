<?php

declare(strict_types=1);

namespace Tests\App\Test\Codeception\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;
use Tests\App\Test\Codeception\Module\StrictWebDriver;

class LocalizationHelper extends Module
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    private $domainRouterFactory;

    /**
     * @var \Tests\App\Test\Codeception\Module\StrictWebDriver
     */
    private $webDriver;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade
     */
    private $unitFacade;

    /**
     * @param \Codeception\TestInterface $test
     */
    public function _before(TestInterface $test): void
    {
        /** @var \Tests\App\Test\Codeception\Helper\SymfonyHelper $symfonyHelper */
        $symfonyHelper = $this->getModule(SymfonyHelper::class);
        /** @var \Tests\App\Test\Codeception\Module\StrictWebDriver $strictWebDriver */
        $strictWebDriver = $this->getModule(StrictWebDriver::class);
        $this->webDriver = $strictWebDriver;
        $this->localization = $symfonyHelper->grabServiceFromContainer(Localization::class);
        $this->domain = $symfonyHelper->grabServiceFromContainer(Domain::class);
        $this->domainRouterFactory = $symfonyHelper->grabServiceFromContainer(DomainRouterFactory::class);
        $this->unitFacade = $symfonyHelper->grabServiceFromContainer(UnitFacade::class);
    }

    /**
     * @param string $id
     * @param string $domain
     * @param array $parameters
     */
    public function seeTranslationFrontend(string $id, string $domain = 'messages', array $parameters = []): void
    {
        $translatedMessage = t($id, $parameters, $domain, $this->getFrontendLocale());
        $this->webDriver->see(strip_tags($translatedMessage));
    }

    /**
     * @param string $id
     * @param string $domain
     * @param array $parameters
     */
    public function dontSeeTranslationFrontend(string $id, string $domain = 'messages', array $parameters = []): void
    {
        $translatedMessage = t($id, $parameters, $domain, $this->getFrontendLocale());
        $this->webDriver->dontSee(strip_tags($translatedMessage));
    }

    /**
     * @param string $id
     * @param string $domain
     * @param array $parameters
     */
    public function seeTranslationAdmin(string $id, string $domain = 'messages', array $parameters = []): void
    {
        $translatedMessage = t($id, $parameters, $domain, $this->getAdminLocale());
        $this->webDriver->see(strip_tags($translatedMessage));
    }

    /**
     * @param string $id
     * @param string $css
     * @param string $domain
     * @param array $parameters
     */
    public function seeTranslationAdminInCss(string $id, string $css, string $domain = 'messages', array $parameters = []): void
    {
        $translatedMessage = t($id, $parameters, $domain, $this->getAdminLocale());
        $this->webDriver->seeInCss(strip_tags($translatedMessage), $css);
    }

    /**
     * @param string $id
     * @param string $domain
     * @param array $parameters
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     */
    public function clickByTranslationAdmin(string $id, string $domain = 'messages', array $parameters = [], $contextSelector = null): void
    {
        $translatedMessage = t($id, $parameters, $domain, $this->getAdminLocale());
        $this->webDriver->clickByText(strip_tags($translatedMessage), $contextSelector);
    }

    /**
     * @param string $id
     * @param string $domain
     * @param array $parameters
     * @param \Facebook\WebDriver\WebDriverBy|\Facebook\WebDriver\WebDriverElement|null $contextSelector
     */
    public function clickByTranslationFrontend(string $id, string $domain = 'messages', array $parameters = [], $contextSelector = null): void
    {
        $translatedMessage = t($id, $parameters, $domain, $this->getFrontendLocale());
        $this->webDriver->clickByText(strip_tags($translatedMessage), $contextSelector);
    }

    /**
     * @param string $id
     * @param string $domain
     * @param array $parameters
     */
    public function checkOptionByLabelTranslationFrontend(string $id, string $domain = 'messages', array $parameters = []): void
    {
        $translatedMessage = t($id, $parameters, $domain, $this->getFrontendLocale());
        $this->webDriver->checkOptionByLabel($translatedMessage);
    }

    /**
     * @return string
     */
    public function getAdminLocale(): string
    {
        return $this->localization->getAdminLocale();
    }

    /**
     * @return string
     */
    public function getFrontendLocale(): string
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @return string
     */
    private function getLocalizedPathOnFirstDomainByRouteName(string $routeName, array $parameters = []): string
    {
        $router = $this->domainRouterFactory->getRouter(Domain::FIRST_DOMAIN_ID);

        return $router->generate($routeName, $parameters);
    }

    /**
     * @param string $routeName
     * @param array $parameters
     */
    public function amOnLocalizedRoute(string $routeName, array $parameters = [])
    {
        $this->webDriver->amOnPage($this->getLocalizedPathOnFirstDomainByRouteName($routeName, $parameters));
    }

    /**
     * @return string
     */
    public function getDefaultUnitName(): string
    {
        return $this->unitFacade->getDefaultUnit()->getName($this->getFrontendLocale());
    }
}
