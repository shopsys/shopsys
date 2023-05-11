<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use App\DataFixtures\Demo\CurrencyDataFixture;
use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceConverter;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Test\ApplicationTestCase;

abstract class GraphQlTestCase extends ApplicationTestCase
{
    protected string $firstDomainUrl;

    /**
     * @inject
     */
    protected BasePriceCalculation $basePriceCalculation;

    /**
     * @inject
     */
    protected PriceConverter $priceConverter;

    /**
     * @inject
     */
    protected CurrencyFacade $currencyFacade;

    /**
     * @inject
     */
    protected VatFacade $vatFacade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runCheckTestEnabledOnCurrentDomain();

        $this->configureCurrentClient(null, null, ['CONTENT_TYPE' => 'application/graphql']);

        $this->firstDomainUrl = $this->domain->getCurrentDomainConfig()->getUrl();
    }

    protected function runCheckTestEnabledOnCurrentDomain(): void
    {
        $enabledOnCurrentDomainChecker = self::getContainer()->get(EnabledOnDomainChecker::class);

        if (!$enabledOnCurrentDomainChecker->isEnabledOnCurrentDomain()) {
            $this->markTestSkipped('Frontend API disabled on domain');
        }
    }

    /**
     * @param string $query
     * @param string $jsonExpected
     * @param string $jsonVariables
     */
    protected function assertQueryWithExpectedJson(string $query, string $jsonExpected, $jsonVariables = '{}'): void
    {
        $this->assertQueryWithExpectedArray(
            $query,
            Json::decode($jsonExpected, Json::FORCE_ARRAY),
            Json::decode($jsonVariables, Json::FORCE_ARRAY)
        );
    }

    /**
     * @param string $query
     * @param array $expected
     * @param array $variables
     */
    protected function assertQueryWithExpectedArray(string $query, array $expected, array $variables = []): void
    {
        $response = $this->getResponseForQuery($query, $variables);

        $this->assertSame(200, $response->getStatusCode());

        $result = $response->getContent();
        $this->assertEquals($expected, Json::decode($result, Json::FORCE_ARRAY), $result);
    }

    /**
     * @param string $query
     * @param array $variables
     * @return array
     */
    protected function getResponseContentForQuery(string $query, array $variables = []): array
    {
        $content = $this->getResponseForQuery($query, $variables)->getContent();

        return Json::decode($content, Json::FORCE_ARRAY);
    }

    /**
     * @param string $query
     * @param array $variables
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getResponseForQuery(string $query, array $variables): Response
    {
        $path = $this->getLocalizedPathOnFirstDomainByRouteName('overblog_graphql_endpoint');

        self::$client->post(
            $path,
            [
                'query' => $query,
                'variables' => Json::encode($variables),
            ],
        );

        return self::$client->getResponse();
    }

    /**
     * @return string
     */
    protected function getLocaleForFirstDomain(): string
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
    }

    /**
     * @param string $uri
     * @return string
     */
    protected function getFullUrlPath(string $uri): string
    {
        return $this->firstDomainUrl . $uri;
    }

    /**
     * @param array $response
     * @return array
     */
    protected function getErrorsFromResponse(array $response): array
    {
        return $response['errors'];
    }

    /**
     * @param array $response
     * @return array
     */
    protected function getErrorsExtensionValidationFromResponse(array $response): array
    {
        return $this->getErrorsFromResponse($response)[0]['extensions']['validation'];
    }

    /**
     * @param array $response
     * @param string $graphQlType
     * @return array
     */
    protected function getResponseDataForGraphQlType(array $response, string $graphQlType): array
    {
        if (!array_key_exists('data', $response)) {
            $this->fail('Invalid GraphQL response: ' . Json::encode($response));
        }

        if (array_key_exists('errors', $response)) {
            $this->fail('GraphQL response contains errors: ' . Json::encode($response));
        }

        if ($response['data'][$graphQlType] === null) {
            $this->fail(
                "Query returned null. If it's an expected state, don't use `getResponseDataForGraphQlType` method for parsing response"
            );
        }

        return $response['data'][$graphQlType];
    }

    /**
     * @param array $response
     * @param string $graphQlType
     */
    protected function assertResponseContainsArrayOfDataForGraphQlType(array $response, string $graphQlType): void
    {
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey($graphQlType, $response['data']);
        $this->assertIsArray($response['data'][$graphQlType]);
    }

    /**
     * @param array $response
     */
    protected function assertResponseContainsArrayOfErrors(array $response): void
    {
        $this->assertArrayHasKey('errors', $response);
        $this->assertIsArray($response['errors']);
    }

    /**
     * @param array $response
     */
    protected function assertResponseContainsArrayOfExtensionValidationErrors(array $response): void
    {
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('extensions', $errors[0]);
        $this->assertArrayHasKey('validation', $errors[0]['extensions']);
        $this->assertIsArray($errors[0]['extensions']['validation']);
    }

    /**
     * @param string $priceWithoutVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param int $quantity
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private function getConvertedPriceToDomainDefaultCurrency(
        string $priceWithoutVat,
        Vat $vat,
        int $quantity = 1
    ): Price {
        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);

        $basePrice = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $this->priceConverter->convertPriceWithoutVatToDomainDefaultCurrencyPrice(
                Money::create($priceWithoutVat),
                $currencyCzk,
                $domainId
            ),
            PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
            $vat,
            $currency
        );

        return $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $basePrice->getPriceWithVat()->multiply($quantity),
            PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
            $vat,
            $currency
        );
    }

    /**
     * @param string $priceWithoutVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param int $quantity
     * @return array
     */
    protected function getSerializedPriceConvertedToDomainDefaultCurrency(
        string $priceWithoutVat,
        Vat $vat,
        int $quantity = 1
    ): array {
        $price = $this->getConvertedPriceToDomainDefaultCurrency($priceWithoutVat, $vat, $quantity);

        return [
            'priceWithVat' => MoneyFormatterHelper::formatWithMaxFractionDigits($price->getPriceWithVat()),
            'priceWithoutVat' => MoneyFormatterHelper::formatWithMaxFractionDigits($price->getPriceWithoutVat()),
            'vatAmount' => MoneyFormatterHelper::formatWithMaxFractionDigits($price->getVatAmount()),
        ];
    }

    /**
     * @param string $priceWithoutVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param int $quantity
     * @return string
     */
    protected function getMutationPriceConvertedToDomainDefaultCurrency(
        string $priceWithoutVat,
        Vat $vat,
        int $quantity = 1
    ): string {
        $price = $this->getConvertedPriceToDomainDefaultCurrency($priceWithoutVat, $vat, $quantity);

        return '{
            priceWithVat: "' . MoneyFormatterHelper::formatWithMaxFractionDigits($price->getPriceWithVat()) . '",
            priceWithoutVat: "' . MoneyFormatterHelper::formatWithMaxFractionDigits($price->getPriceWithoutVat()) . '",
            vatAmount: "' . MoneyFormatterHelper::formatWithMaxFractionDigits($price->getVatAmount()) . '"
        }';
    }

    /**
     * @param string $price
     * @return string
     */
    protected function getFormattedMoneyAmountConvertedToDomainDefaultCurrency(string $price): string
    {
        /** @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currencyCzk */
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK);
        $money = $this->priceConverter->convertPriceWithoutVatToDomainDefaultCurrencyPrice(
            Money::create($price),
            $currencyCzk,
            Domain::FIRST_DOMAIN_ID
        );

        return MoneyFormatterHelper::formatWithMaxFractionDigits($money);
    }
}
