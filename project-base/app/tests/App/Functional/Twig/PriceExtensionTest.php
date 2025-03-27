<?php

declare(strict_types=1);

namespace Tests\App\Functional\Twig;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Localization\IntlCurrencyRepository;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFactory;
use Shopsys\FrameworkBundle\Twig\PriceExtension;
use Tests\App\Test\FunctionalTestCase;

class PriceExtensionTest extends FunctionalTestCase
{
    protected const NBSP = "\xc2\xa0";

    /**
     * @inject
     */
    private IntlCurrencyRepository $intlCurrencyRepository;

    /**
     * @inject
     */
    private CurrencyFormatterFactory $currencyFormatterFactory;

    /**
     * @inject
     */
    private CurrencyFactory $currencyFactory;

    /**
     * @inject
     */
    private CurrencyDataFactory $currencyDataFactory;

    /**
     * @inject
     */
    private AdminDomainTabsFacade $adminDomainTabsFacade;

    /**
     * @return array[]
     */
    public static function priceFilterSingledomainDataProvider(): array
    {
        return [
            [
                'input' => Money::create(12),
                'locale' => 'en',
                'result' => 'CZK12.00',
            ], [
                'input' => Money::create('12.00'),
                'locale' => 'en',
                'result' => 'CZK12.00',
            ], [
                'input' => Money::create('12.600'),
                'locale' => 'en',
                'result' => 'CZK12.60',
            ], [
                'input' => Money::create('12.630000'),
                'locale' => 'en',
                'result' => 'CZK12.63',
            ], [
                'input' => Money::create('12.638000'),
                'locale' => 'en',
                'result' => 'CZK12.638',
            ], [
                'input' => Money::create('12.630000'),
                'locale' => 'en',
                'result' => 'CZK12.63',
            ], [
                'input' => Money::create('123456789.123456789'),
                'locale' => 'en',
                'result' => 'CZK123,456,789.123456789',
            ], [
                'input' => Money::create('123456789.123456789123456789'),
                'locale' => 'en',
                'result' => 'CZK123,456,789.1234567891',
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $input
     * @param string $result
     * @param string $locale
     */
    #[DataProvider('priceFilterSingledomainDataProvider')]
    #[Group('multidomain')]
    public function testPriceFilterSingledomain(Money $input, string $locale, string $result): void
    {
        $this->domain->switchDomainById(Domain::FIRST_DOMAIN_ID);

        $priceExtension = $this->getPriceExtensionWithMockedConfiguration(Domain::FIRST_DOMAIN_ID, $locale);

        $this->assertSame($result, $priceExtension->priceFilter($input));
    }

    /**
     * @return array[]
     */
    public static function priceFilterMultidomainDataProvider(): array
    {
        return [
            ['input' => Money::create(12), 'domainId' => Domain::FIRST_DOMAIN_ID,
                'locale' => 'en', 'result' => 'CZK12.00'],
            ['input' => Money::create('12.00'), 'domainId' => Domain::FIRST_DOMAIN_ID,
                'locale' => 'en', 'result' => 'CZK12.00'],
            ['input' => Money::create('12.600'), 'domainId' => Domain::FIRST_DOMAIN_ID,
                'locale' => 'en', 'result' => 'CZK12.60'],
            ['input' => Money::create('12.630000'), 'domainId' => Domain::FIRST_DOMAIN_ID,
                'locale' => 'en', 'result' => 'CZK12.63'],
            ['input' => Money::create('12.638000'), 'domainId' => Domain::FIRST_DOMAIN_ID,
                'locale' => 'en', 'result' => 'CZK12.638'],
            ['input' => Money::create('12.630000'), 'domainId' => Domain::FIRST_DOMAIN_ID,
                'locale' => 'en', 'result' => 'CZK12.63'],
            [
                'input' => Money::create('123456789.123456789'),
                'domainId' => Domain::FIRST_DOMAIN_ID,
                'locale' => 'en',
                'result' => 'CZK123,456,789.123456789',
            ],
            [
                'input' => Money::create('123456789.123456789123456789'),
                'domainId' => Domain::FIRST_DOMAIN_ID,
                'locale' => 'en',
                'result' => 'CZK123,456,789.1234567891',
            ],
            ['input' => Money::create(
                12,
            ), 'domainId' => Domain::SECOND_DOMAIN_ID,
                'locale' => 'cs', 'result' => '12,00' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.00',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID,
                'locale' => 'cs', 'result' => '12,00' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.600',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID,
                'locale' => 'cs', 'result' => '12,60' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.630000',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID,
                'locale' => 'cs', 'result' => '12,63' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.638000',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID,
                'locale' => 'cs', 'result' => '12,638' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.630000',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID,
                'locale' => 'cs', 'result' => '12,63' . self::NBSP . '€'],
            [
                'input' => Money::create('123456789.123456789'),
                'domainId' => Domain::SECOND_DOMAIN_ID,
                'locale' => 'cs',
                'result' => '123' . self::NBSP . '456' . self::NBSP . '789,123456789' . self::NBSP . '€',
            ],
            [
                'input' => Money::create('123456789.123456789123456789'),
                'domainId' => Domain::SECOND_DOMAIN_ID,
                'locale' => 'cs',
                'result' => '123' . self::NBSP . '456' . self::NBSP . '789,1234567891' . self::NBSP . '€',
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $input
     * @param int $domainId
     * @param string $locale
     * @param string $result
     */
    #[DataProvider('priceFilterMultidomainDataProvider')]
    #[Group('multidomain')]
    public function testPriceFilterMultidomain(Money $input, int $domainId, string $locale, string $result): void
    {
        $this->domain->switchDomainById($domainId);

        $priceExtension = $this->getPriceExtensionWithMockedConfiguration($domainId, $locale);

        $this->assertSame($result, $priceExtension->priceFilter($input));
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Twig\PriceExtension
     */
    private function getPriceExtensionWithMockedConfiguration(int $domainId, string $locale): PriceExtension
    {
        $domain1DefaultCurrencyData = $this->currencyDataFactory->create();
        $domain1DefaultCurrencyData->name = 'Czech crown';
        $domain1DefaultCurrencyData->code = Currency::CODE_CZK;
        $domain1DefaultCurrencyData->exchangeRate = '1';
        $domain1DefaultCurrencyData->minFractionDigits = 2;
        $domain1DefaultCurrencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;

        $domain2DefaultCurrencyData = $this->currencyDataFactory->create();
        $domain2DefaultCurrencyData->name = 'Euro';
        $domain2DefaultCurrencyData->code = Currency::CODE_EUR;
        $domain2DefaultCurrencyData->exchangeRate = '25';
        $domain1DefaultCurrencyData->minFractionDigits = 2;
        $domain1DefaultCurrencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;

        $domain1DefaultCurrency = $this->currencyFactory->create($domain1DefaultCurrencyData);
        $domain2DefaultCurrency = $this->currencyFactory->create($domain2DefaultCurrencyData);

        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade|\PHPUnit\Framework\MockObject\MockObject $currencyFacadeMock */
        $currencyFacadeMock = $this->getMockBuilder(CurrencyFacade::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDomainDefaultCurrencyByDomainId', 'getDefaultCurrency'])
            ->getMock();
        $currencyFacadeMock
            ->method('getDomainDefaultCurrencyByDomainId')
            ->willReturnMap([
                [Domain::FIRST_DOMAIN_ID, $domain1DefaultCurrency],
                [Domain::SECOND_DOMAIN_ID, $domain2DefaultCurrency],
            ]);
        $currencyFacadeMock
            ->method('getDefaultCurrency')
            ->willReturn($domain1DefaultCurrency);
        $administratorFrontSecurityFacadeMock = $this->getMockBuilder(AdministratorFrontSecurityFacade::class)
            ->disableOriginalConstructor()
            ->getMock();

        $domain = $this->getMockBuilder(Domain::class)
            ->disableOriginalConstructor()
            ->getMock();

        $domain->method('getId')->willReturn($domainId);

        $localizationMock = $this->getMockBuilder(Localization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $localizationMock->method('getRequestLocale')->willReturn($locale);

        return new PriceExtension(
            $currencyFacadeMock,
            $domain,
            $localizationMock,
            $this->intlCurrencyRepository,
            $this->currencyFormatterFactory,
            $this->adminDomainTabsFacade,
        );
    }
}
