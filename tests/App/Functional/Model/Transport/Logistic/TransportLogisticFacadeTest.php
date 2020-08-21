<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Transport\Logistic;

use App\Component\Domain\Domain;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Product\Package\ProductPackageData;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportPackage\TransportPackageData;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\App\Test\TransactionFunctionalTestCase;

class TransportLogisticFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private $product1;

    /**
     * @var \App\Model\Product\Product
     */
    private $product2;

    /**
     * @var \App\Model\Transport\Transport
     */
    private $transportCzechPost;

    /**
     * @var \App\Model\Transport\Transport
     */
    private $transportPPL;

    /**
     * @var \App\Model\Transport\Transport
     */
    private $transportPallet;

    /**
     * @var \App\Model\Cart\CartFacade
     * @inject
     */
    private $cartFacade;

    /**
     * @var \App\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \App\Model\Product\ProductDataFactory
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \App\Model\Transport\Logistic\TransportLogisticFacade
     * @inject
     */
    private $transportLogisticFacade;

    /**
     * @var \App\Model\Transport\TransportFacade
     * @inject
     */
    private $transportFacade;

    /**
     * @var \App\Model\Payment\PaymentFacade
     * @inject
     */
    private $paymentFacade;

    /**
     * @var \App\Model\Transport\TransportDataFactory
     * @inject
     */
    private $transportDataFactory;

    /**
     * @var \App\Model\Transport\TransportPackage\TransportPackageDataFactory
     * @inject
     */
    private $transportPackageDataFactory;

    /**
     * @var \App\Model\Product\Package\ProductPackageFacade
     * @inject
     */
    private $productPackageFacade;

    /**
     * @var \App\Model\Product\Package\ProductPackageDataFactory
     * @inject
     */
    private $productPackageDataFactory;

    public function testPackageTransportIsNotEnabledIfOneProductDoesNotSupportIt(): void
    {
        $this->prepareDefaultUseCaseData();

        $productData2 = $this->productDataFactory->createFromProduct($this->product2);
        $productData2->canBeShippedAsPackage[Domain::FIRST_DOMAIN_ID] = false;
        $this->productFacade->edit($this->product2->getId(), $productData2);

        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertContainsTransport($this->transportPallet, $transports);
        self::assertNotContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testPalletTransportIsNotEnabledIfAllProductsCanBeShippedByPackage(): void
    {
        $this->prepareDefaultUseCaseData();

        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertNotContainsTransport($this->transportPallet, $transports);
        self::assertContainsTransport($this->transportCzechPost, $transports);
        self::assertContainsTransport($this->transportPPL, $transports);
    }

    public function testMaxWeightIsReachedAndOnePackageTransportIsNotAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 30, 20, 10, 10),
            $this->createProductPackageData(2, 30, 20, 15, 12),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 10, 5),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(10, '100', null, null, null, null, null),
            $this->createTransportPackageData(49, '200', null, null, null, null, null),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(48, '100', null, null, null, null, null),
        ]);

        // Total weight is 2 * (10 + 12) + 5 = 49
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertNotContainsTransport($this->transportPallet, $transports);
        self::assertContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testMaxWeightIsReachedAndPalletTransportIsAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 30, 20, 10, 10),
            $this->createProductPackageData(2, 30, 20, 15, 12),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 10, 5),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(10, '100', null, null, null, null, null),
            $this->createTransportPackageData(48, '200', null, null, null, null, null),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(48, '100', null, null, null, null, null),
        ]);

        // Total weight is 2 * (10 + 12) + 5 = 49
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertContainsTransport($this->transportPallet, $transports);
        self::assertNotContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testMaxProductPackagesCountIsReachedAndOnePackageTransportIsNotAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 30, 20, 10, 1),
            $this->createProductPackageData(2, 30, 20, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 10, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', 4, null, null, null, null),
            $this->createTransportPackageData(9999, '200', 5, null, null, null, null),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', 4, null, null, null, null),
        ]);

        // Total packages count is 2 * 2 + 1 = 5
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertNotContainsTransport($this->transportPallet, $transports);
        self::assertContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testMaxProductPackagesCountIsReachedAndPalletTransportIsAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 30, 20, 10, 1),
            $this->createProductPackageData(2, 30, 20, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 10, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', 1, null, null, null, null),
            $this->createTransportPackageData(9999, '200', 2, null, null, null, null),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', 4, null, null, null, null),
        ]);

        // Total packages count is 2 * 2 + 1 = 5
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertContainsTransport($this->transportPallet, $transports);
        self::assertNotContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testMaxGirthIsReachedAndOnePackageTransportIsNotAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 60, 20, 10, 1),
            $this->createProductPackageData(2, 50, 20, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 5, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', null, 150, null, null, null),
            $this->createTransportPackageData(9999, '200', null, 290, null, null, null),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', null, 289, null, null, null),
        ]);

        // Total girth is 60 + 2 * 60 + 2 * 55 = 290
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertNotContainsTransport($this->transportPallet, $transports);
        self::assertContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testMaxGirthIsReachedAndPalletTransportIsAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 60, 20, 10, 1),
            $this->createProductPackageData(2, 50, 20, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 5, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', null, 150, null, null, null),
            $this->createTransportPackageData(9999, '200', null, 289, null, null, null),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', null, 289, null, null, null),
        ]);

        // Total girth is 60 + 2 * 60 + 2 * 55 = 290
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertContainsTransport($this->transportPallet, $transports);
        self::assertNotContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testDimension1IsOverLimitAndOnePackageTransportIsNotAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 10000, 20, 10, 1),
            $this->createProductPackageData(2, 50, 20, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 5, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', null, null, 30, 9000, 9000),
            $this->createTransportPackageData(9999, '200', null, null, 10000, 9000, 9000),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', null, null, 9999, 9000, 9000),
        ]);

        // Limit of dimension1 is 10000 (max of 10000, 50, 30)
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertNotContainsTransport($this->transportPallet, $transports);
        self::assertContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testDimension1IsOverLimitAndPalletTransportIsAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 10000, 20, 10, 1),
            $this->createProductPackageData(2, 50, 20, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 5, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', null, null, 30, 9000, 9000),
            $this->createTransportPackageData(9999, '200', null, null, 9999, 9000, 9000),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', null, null, 9999, 9000, 9000),
        ]);

        // Limit of dimension1 is 1000 (max of 10000, 50, 30)
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertContainsTransport($this->transportPallet, $transports);
        self::assertNotContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testDimension2IsOverLimitAndOnePackageTransportIsNotAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 10000, 10, 10, 1),
            $this->createProductPackageData(2, 9000, 19, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 8000, 1000, 5, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', null, null, 10000, 100, 99),
            $this->createTransportPackageData(9999, '200', null, null, 10000, 1000, 99),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', null, null, 10000, 999, 99),
        ]);

        // Limit of dimension2 is 1000 (max of 10, 19, 1000)
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertNotContainsTransport($this->transportPallet, $transports);
        self::assertContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testDimension2IsOverLimitAndPalletTransportIsAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 10000, 10, 10, 1),
            $this->createProductPackageData(2, 9000, 19, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 8000, 1000, 5, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', null, null, 10000, 100, 99),
            $this->createTransportPackageData(9999, '200', null, null, 10000, 999, 99),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', null, null, 10000, 999, 99),
        ]);

        // Limit of dimension2 is 1000 (max of 10, 19, 1000)
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertContainsTransport($this->transportPallet, $transports);
        self::assertNotContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testDimension3IsOverLimitAndOnePackageTransportIsNotAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 60, 10, 10, 1),
            $this->createProductPackageData(2, 50, 19, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 5, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', null, null, 9999, 9999, 30),
            $this->createTransportPackageData(9999, '200', null, null, 9999, 9999, 55),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', null, null, 9999, 9999, 54),
        ]);

        // Limit of dimension3 is 2 * (10 + 15) + 5 = 55
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertNotContainsTransport($this->transportPallet, $transports);
        self::assertContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    public function testDimension3IsOverLimitAndPalletTransportIsAllowed(): void
    {
        $this->prepareDefaultUseCaseData();

        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 60, 10, 10, 1),
            $this->createProductPackageData(2, 50, 19, 15, 1),
        ]);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 5, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', null, null, 9999, 9999, 30),
            $this->createTransportPackageData(9999, '200', null, null, 9999, 9999, 54),
        ]);
        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '100', null, null, 9999, 9999, 54),
        ]);

        // Limit of dimension3 is 2 * (10 + 15) + 5 = 55
        $transports = $this->getAllowedTransportForCurrentCart();

        self::assertContainsTransport($this->transportPallet, $transports);
        self::assertNotContainsTransport($this->transportCzechPost, $transports);
        self::assertNotContainsTransport($this->transportPPL, $transports);
    }

    /**
     * self::assertContains() dumps whole entities on fail and it is too long
     * @param \App\Model\Transport\Transport $needle
     * @param \App\Model\Transport\Transport[] $haystack
     */
    private static function assertContainsTransport(Transport $needle, array $haystack): void
    {
        if (in_array($needle, $haystack, true) === false) {
            $message = sprintf('Transport "%s" (id=%d) should be in array', $needle->getName(), $needle->getId());
            self::fail($message);
        }
    }

    /**
     * self::assertNotContains() dumps whole entities on fail and it is too long
     * @param \App\Model\Transport\Transport $needle
     * @param \App\Model\Transport\Transport[] $haystack
     */
    private static function assertNotContainsTransport(Transport $needle, array $haystack): void
    {
        if (in_array($needle, $haystack, true) === true) {
            $message = sprintf('Transport "%s" (id=%d) should not be in array', $needle->getName(), $needle->getId());
            self::fail($message);
        }
    }

    /**
     * @return \App\Model\Transport\Transport[]
     */
    private function getAllowedTransportForCurrentCart(): array
    {
        $payments = $this->paymentFacade->getVisibleOnCurrentDomain();
        $transports = $this->transportFacade->getVisibleOnCurrentDomain($payments);

        return $this->transportLogisticFacade->filterAllowedTransportsForCurrentCart($transports);
    }

    private function prepareDefaultUseCaseData(): void
    {
        $this->product1 = $this->productFacade->getById(1);
        $this->product2 = $this->productFacade->getById(2);
        $this->transportCzechPost = $this->getReference(TransportDataFixture::TRANSPORT_CZECH_POST);
        $this->transportPPL = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        $this->transportPallet = $this->getReference(TransportDataFixture::TRANSPORT_PALLET);

        $this->cartFacade->addProductToCart($this->product1->getId(), 2);
        $this->cartFacade->addProductToCart($this->product2->getId(), 1);

        $productData1 = $this->productDataFactory->createFromProduct($this->product1);
        $productData1->canBeShippedAsPackage[Domain::FIRST_DOMAIN_ID] = true;
        $this->productFacade->edit($this->product1->getId(), $productData1);
        $this->productPackageFacade->updateProductPackages($this->product1, [
            $this->createProductPackageData(1, 30, 20, 10, 1),
            $this->createProductPackageData(2, 30, 20, 15, 1),
        ]);

        $productData2 = $this->productDataFactory->createFromProduct($this->product2);
        $productData2->canBeShippedAsPackage[Domain::FIRST_DOMAIN_ID] = true;
        $this->productFacade->edit($this->product2->getId(), $productData2);
        $this->productPackageFacade->updateProductPackages($this->product2, [
            $this->createProductPackageData(1, 30, 20, 10, 1),
        ]);

        $this->setTransportPackages($this->transportCzechPost, [
            $this->createTransportPackageData(9999, '100', null, null, null, null, null),
            $this->createTransportPackageData(9999, '200', null, null, null, null, null),
        ]);

        $this->setTransportPackages($this->transportPPL, [
            $this->createTransportPackageData(9999, '300', null, null, null, null, null),
        ]);
    }

    /**
     * @param int $position
     * @param int $height
     * @param int $length
     * @param int $width
     * @param int $weight
     * @return \App\Model\Product\Package\ProductPackageData
     */
    private function createProductPackageData(int $position, int $height, int $length, int $width, int $weight): ProductPackageData
    {
        $productPackageData = $this->productPackageDataFactory->create();
        $productPackageData->position = $position;
        $productPackageData->height = $height;
        $productPackageData->length = $length;
        $productPackageData->width = $width;
        $productPackageData->weight = $weight;

        return $productPackageData;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Transport\TransportPackage\TransportPackageData[] $transportPackagesData
     */
    private function setTransportPackages(Transport $transport, array $transportPackagesData): void
    {
        $transportData1 = $this->transportDataFactory->createFromTransport($transport);
        $transportData1->transportPackages = $transportPackagesData;
        $this->transportFacade->edit($transport, $transportData1);
    }

    /**
     * @param int $maxWeight
     * @param string $priceWithVat
     * @param int|null $maxProductPackagesCount
     * @param int|null $maxGirth
     * @param int|null $dimension1
     * @param int|null $dimension2
     * @param int|null $dimension3
     * @return \App\Model\Transport\TransportPackage\TransportPackageData
     */
    private function createTransportPackageData(
        int $maxWeight,
        string $priceWithVat,
        ?int $maxProductPackagesCount,
        ?int $maxGirth,
        ?int $dimension1,
        ?int $dimension2,
        ?int $dimension3
    ): TransportPackageData {
        $transportPackageData = $this->transportPackageDataFactory->create();
        $transportPackageData->domainId = Domain::FIRST_DOMAIN_ID;
        $transportPackageData->maxWeight = $maxWeight;
        $transportPackageData->priceWithVat = Money::create($priceWithVat);
        $transportPackageData->maxProductPackagesCount = $maxProductPackagesCount;
        $transportPackageData->maxGirth = $maxGirth;
        $transportPackageData->dimension1 = $dimension1;
        $transportPackageData->dimension2 = $dimension2;
        $transportPackageData->dimension3 = $dimension3;

        return $transportPackageData;
    }
}
