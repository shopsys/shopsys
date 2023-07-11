<?php

declare(strict_types=1);

namespace Tests\App\Functional\Twig;

use DateTimeZone;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Localization\IntlCurrencyRepository;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFactoryInterface;
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
    private CurrencyFactoryInterface $currencyFactory;

    /**
     * @inject
     */
    private CurrencyDataFactoryInterface $currencyDataFactory;

    protected function setUp(): void
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfig1 = new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com', 'example', 'en', $defaultTimeZone);
        $domainConfig2 = new DomainConfig(Domain::SECOND_DOMAIN_ID, 'http://example.com', 'example', 'cs', $defaultTimeZone);

        /** @var \Shopsys\FrameworkBundle\Component\Setting\Setting|\PHPUnit\Framework\MockObject\MockObject $settingMock */
        $settingMock = $this->getMockBuilder(Setting::class)
            ->disableOriginalConstructor()
            ->setMethods(['getForDomain'])
            ->getMock();
        $settingMock
            ->method('getForDomain')
            ->with($this->equalTo(Setting::DOMAIN_DATA_CREATED))
            ->willReturn(true);

        $this->domain = new Domain([$domainConfig1, $domainConfig2], $settingMock);

        parent::setUp();
    }

    public function priceFilterDataProvider()
    {
        return [
            ['input' => Money::create(12), 'domainId' => Domain::FIRST_DOMAIN_ID, 'result' => 'CZK12.00'],
            ['input' => Money::create('12.00'), 'domainId' => Domain::FIRST_DOMAIN_ID, 'result' => 'CZK12.00'],
            ['input' => Money::create('12.600'), 'domainId' => Domain::FIRST_DOMAIN_ID, 'result' => 'CZK12.60'],
            ['input' => Money::create('12.630000'), 'domainId' => Domain::FIRST_DOMAIN_ID, 'result' => 'CZK12.63'],
            ['input' => Money::create('12.638000'), 'domainId' => Domain::FIRST_DOMAIN_ID, 'result' => 'CZK12.638'],
            ['input' => Money::create('12.630000'), 'domainId' => Domain::FIRST_DOMAIN_ID, 'result' => 'CZK12.63'],
            [
                'input' => Money::create('123456789.123456789'),
                'domainId' => Domain::FIRST_DOMAIN_ID,
                'result' => 'CZK123,456,789.123456789',
            ],
            [
                'input' => Money::create('123456789.123456789123456789'),
                'domainId' => Domain::FIRST_DOMAIN_ID,
                'result' => 'CZK123,456,789.1234567891',
            ],
            ['input' => Money::create(
                12,
            ), 'domainId' => Domain::SECOND_DOMAIN_ID, 'result' => '12,00' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.00',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID, 'result' => '12,00' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.600',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID, 'result' => '12,60' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.630000',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID, 'result' => '12,63' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.638000',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID, 'result' => '12,638' . self::NBSP . '€'],
            ['input' => Money::create(
                '12.630000',
            ), 'domainId' => Domain::SECOND_DOMAIN_ID, 'result' => '12,63' . self::NBSP . '€'],
            [
                'input' => Money::create('123456789.123456789'),
                'domainId' => Domain::SECOND_DOMAIN_ID,
                'result' => '123' . self::NBSP . '456' . self::NBSP . '789,123456789' . self::NBSP . '€',
            ],
            [
                'input' => Money::create('123456789.123456789123456789'),
                'domainId' => Domain::SECOND_DOMAIN_ID,
                'result' => '123' . self::NBSP . '456' . self::NBSP . '789,1234567891' . self::NBSP . '€',
            ],
        ];
    }

    /**
     * @dataProvider priceFilterDataProvider
     * @param mixed $input
     * @param mixed $domainId
     * @param mixed $result
     */
    public function testPriceFilter($input, $domainId, $result)
    {
        $this->domain->switchDomainById($domainId);

        $priceExtension = $this->getPriceExtensionWithMockedConfiguration();

        $this->assertSame($result, $priceExtension->priceFilter($input));
    }

    /**
     * @return \Shopsys\FrameworkBundle\Twig\PriceExtension
     */
    private function getPriceExtensionWithMockedConfiguration(): PriceExtension
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
            ->setMethods(['getDomainDefaultCurrencyByDomainId', 'getDefaultCurrency'])
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
        $localization = new Localization($this->domain, 'en');

        return new PriceExtension(
            $currencyFacadeMock,
            $this->domain,
            $localization,
            $this->intlCurrencyRepository,
            $this->currencyFormatterFactory,
        );
    }
}
