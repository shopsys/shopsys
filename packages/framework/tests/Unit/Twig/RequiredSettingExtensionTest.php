<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\Settings\PhonePrefixSettingsFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;
use Shopsys\FrameworkBundle\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Twig\RequiredSettingExtension;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Routing\RouterInterface;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class RequiredSettingExtensionTest extends TestCase
{
    private const string XSS_PAYLOAD = '<img src=x onerror="alert(1)">';
    private const string ESCAPED_XSS_PAYLOAD = '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;';

    public function testStockAndParameterNamesAreEscapedInsideWarningMarkup(): void
    {
        $this->injectTranslatorStub();

        $stockFacade = $this->createStub(StockFacade::class);
        $stockFacade->method('getCount')->willReturn(1);
        $stockFacade
            ->method('getDefaultButDisabledStockDomains')
            ->willReturn([['stockId' => 1, 'stockName' => self::XSS_PAYLOAD, 'domainId' => Domain::FIRST_DOMAIN_ID]]);

        $parameter = $this->createStub(Parameter::class);
        $parameter->method('getId')->willReturn(2);
        $parameter->method('getName')->willReturn(self::XSS_PAYLOAD);

        $parameterFacade = $this->createStub(ParameterFacade::class);
        $parameterFacade->method('getCountOfSliderParametersWithoutTheirsNumericValueFilled')->willReturn(1);
        $parameterFacade->method('getSliderParametersWithoutTheirsNumericValueFilled')->willReturn([$parameter]);

        $requiredSettingExtension = $this->createRequiredSettingExtension($stockFacade, $parameterFacade, 'example.com');

        $renderedRequiredSettings = (string)$requiredSettingExtension->renderRequiredSettings();

        $this->assertStringContainsString(
            'Warehouse <a href="/admin_stock_edit">' . self::ESCAPED_XSS_PAYLOAD . '</a> is set as default on domain "example.com" but is not enabled there.',
            $renderedRequiredSettings,
        );
        $this->assertStringContainsString(
            '<ul><li><a href="/admin_parametervalues_edit">' . self::ESCAPED_XSS_PAYLOAD . '</a></li></ul>',
            $renderedRequiredSettings,
        );
        $this->assertStringNotContainsString('<img', $renderedRequiredSettings);
    }

    public function testDomainNameIsEscapedInsideWarningMarkup(): void
    {
        $this->injectTranslatorStub();

        $stockFacade = $this->createStub(StockFacade::class);
        $stockFacade->method('getCount')->willReturn(1);
        $stockFacade->method('getDomainIdsWithoutDefaultStock')->willReturn([Domain::FIRST_DOMAIN_ID]);

        $requiredSettingExtension = $this->createRequiredSettingExtension(
            $stockFacade,
            $this->createStub(ParameterFacade::class),
            self::XSS_PAYLOAD,
        );

        $renderedRequiredSettings = (string)$requiredSettingExtension->renderRequiredSettings();

        $this->assertStringContainsString(
            '<a href="/admin_stock_list">Domain "' . self::ESCAPED_XSS_PAYLOAD . '" has no default warehouse.</a>',
            $renderedRequiredSettings,
        );
        $this->assertStringNotContainsString('<img', $renderedRequiredSettings);
    }

    private function createRequiredSettingExtension(
        StockFacade $stockFacade,
        ParameterFacade $parameterFacade,
        string $domainName,
    ): RequiredSettingExtension {
        $twig = new Environment(new ArrayLoader([
            '@ShopsysAdministration/partial/required_settings.html.twig' => '{% for message in requiredSettingsMessages %}<div>{{ message|raw }}</div>{% endfor %}',
        ]));

        $router = $this->createStub(RouterInterface::class);
        $router
            ->method('generate')
            ->willReturnCallback(static fn (string $routeName): string => '/' . $routeName);

        $domain = $this->createStub(Domain::class);
        $domain->method('getDomainConfigById')->willReturn(DomainConfigHelper::getDomainConfig(name: $domainName));

        $countryFacade = $this->createStub(CountryFacade::class);
        $countryFacade->method('getCount')->willReturn(1);

        return new RequiredSettingExtension(
            $twig,
            $router,
            $domain,
            $this->createStub(Setting::class),
            $this->createStub(MailTemplateFacade::class),
            $parameterFacade,
            $stockFacade,
            $countryFacade,
            $this->createStub(PhonePrefixSettingsFacade::class),
            $this->createStub(ClosedDayFacade::class),
            new MockClock('2026-06-01'),
            $this->createStub(PaymentFacade::class),
            $this->createStub(TransportFacade::class),
            $this->createStub(ProductReviewEnabledChecker::class),
        );
    }

    private function injectTranslatorStub(): void
    {
        $translator = $this->createStub(Translator::class);
        $translator
            ->method('trans')
            ->willReturnCallback(static fn (string $id, array $parameters = []): string => strtr($id, $parameters));

        Translator::injectSelf($translator);
    }
}
