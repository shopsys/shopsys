<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Admin;

use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\Group;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository;
use Shopsys\FrameworkBundle\Twig\PriceExtension;
use Tests\App\Test\ApplicationTestCase;

final class ProductListPriceCurrencyTest extends ApplicationTestCase
{
    /**
     * @inject
     */
    private PricingGroupSettingFacade $pricingGroupSettingFacade;

    /**
     * @inject
     */
    private ProductManualInputPriceRepository $productManualInputPriceRepository;

    /**
     * @inject
     */
    private PriceExtension $priceExtension;

    /**
     * @inject
     */
    private AdministratorLocalizationFacade $administratorLocalizationFacade;

    /**
     * @inject
     */
    private CurrencyDataFactory $currencyDataFactory;

    #[Group('multidomain')]
    public function testProductListPriceIsListedInCurrencyOfDomainUsingDefaultAdministrationCurrency(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $currencyCzk = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
        $this->currencyFacade->setDefaultCurrency($currencyCzk);
        $expectedPriceCellText = $this->getFormattedManualInputPrice($product, Domain::SECOND_DOMAIN_ID);
        $firstDomainPriceCellText = $this->getFormattedManualInputPrice($product, Domain::FIRST_DOMAIN_ID);

        $priceCellText = $this->getProductListPriceCellText($product);

        $this->assertSame($expectedPriceCellText, $priceCellText);
        $this->assertNotSame($firstDomainPriceCellText, $priceCellText);
    }

    public function testProductListPriceIsEmptyWhenNoDomainUsesDefaultAdministrationCurrency(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $currencyData = $this->currencyDataFactory->create();
        $currencyData->name = 'US dollar';
        $currencyData->code = 'USD';
        $currencyData->exchangeRate = '20';
        $this->currencyFacade->setDefaultCurrency($this->currencyFacade->create($currencyData));

        $priceCellText = $this->getProductListPriceCellText($product);

        $this->assertSame('', $priceCellText);
    }

    private function getProductListPriceCellText(Product $product): string
    {
        $client = $this->configureCurrentClient('admin', 'admin123');
        $client->catchExceptions(false);

        $crawler = $client->request('GET', '/admin/product/list/');

        $this->assertResponseStatusCodeSame(200);
        $productRow = $crawler->filter(sprintf('a[href$="/admin/product/edit/%d"]', $product->getId()))->first()->closest('tr');

        return trim($productRow->filter('td.text-end')->first()->text());
    }

    private function getFormattedManualInputPrice(Product $product, int $domainId): string
    {
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        $manualInputPrice = $this->productManualInputPriceRepository->findByProductAndPricingGroup($product, $pricingGroup);
        $this->assertNotNull($manualInputPrice);
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $this->priceExtension->priceTextWithCurrencyByCurrencyIdAndLocaleFilter(
            $manualInputPrice->getInputPrice(),
            $currency->getId(),
            $this->administratorLocalizationFacade->getDefaultAdminLocale(),
        );
    }
}
