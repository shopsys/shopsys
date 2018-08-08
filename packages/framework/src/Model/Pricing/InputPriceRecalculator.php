<?php

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductService;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class InputPriceRecalculator
{
    const BATCH_SIZE = 500;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\InputPriceCalculation
     */
    private $inputPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation
     */
    private $basePriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    private $paymentPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    private $transportPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductService
     */
    private $productService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    private $pricingSetting;

    public function __construct(
        EntityManagerInterface $em,
        InputPriceCalculation $inputPriceCalculation,
        BasePriceCalculation $basePriceCalculation,
        PaymentPriceCalculation $paymentPriceCalculation,
        TransportPriceCalculation $transportPriceCalculation,
        ProductService $productService,
        PricingSetting $pricingSetting
    ) {
        $this->em = $em;
        $this->inputPriceCalculation = $inputPriceCalculation;
        $this->basePriceCalculation = $basePriceCalculation;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->productService = $productService;
        $this->pricingSetting = $pricingSetting;
    }

    public function recalculateToInputPricesWithoutVat(): void
    {
        $this->recalculateInputPriceForNewType(PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);
    }

    public function recalculateToInputPricesWithVat(): void
    {
        $this->recalculateInputPriceForNewType(PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);
    }

    private function recalculateInputPriceForNewType(string $newInputPriceType): void
    {
        $this->recalculateProductsInputPriceForNewType($newInputPriceType);
        $this->recalculateTransportsInputPriceForNewType($newInputPriceType);
        $this->recalculatePaymentsInputPriceForNewType($newInputPriceType);
    }

    private function recalculateProductsInputPriceForNewType(string $toInputPriceType): void
    {
        $query = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->getQuery();

        $this->batchProcessQuery($query, function (Product $product) use ($toInputPriceType): void {
            $productPrice = $this->basePriceCalculation->calculateBasePrice(
                $product->getPrice(),
                $this->pricingSetting->getInputPriceType(),
                $product->getVat()
            );

            $newInputPrice = $this->inputPriceCalculation->getInputPrice(
                $toInputPriceType,
                $productPrice->getPriceWithVat(),
                $product->getVat()->getPercent()
            );

            $this->productService->setInputPrice($product, $newInputPrice);
        });
    }

    private function recalculatePaymentsInputPriceForNewType(string $toInputPriceType): void
    {
        $query = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Payment::class, 'p')
            ->getQuery();

        $this->batchProcessQuery($query, function (Payment $payment) use ($toInputPriceType): void {
            foreach ($payment->getPrices() as $paymentInputPrice) {
                $paymentPrice = $this->paymentPriceCalculation->calculateIndependentPrice(
                    $payment,
                    $paymentInputPrice->getCurrency()
                );

                $newInputPrice = $this->inputPriceCalculation->getInputPrice(
                    $toInputPriceType,
                    $paymentPrice->getPriceWithVat(),
                    $payment->getVat()->getPercent()
                );

                $paymentInputPrice->setPrice($newInputPrice);
            }
        });
    }

    private function recalculateTransportsInputPriceForNewType(string $toInputPriceType): void
    {
        $query = $this->em->createQueryBuilder()
            ->select('t')
            ->from(Transport::class, 't')
            ->getQuery();

        $this->batchProcessQuery($query, function (Transport $transport) use ($toInputPriceType): void {
            foreach ($transport->getPrices() as $transportInputPrice) {
                $transportPrice = $this->transportPriceCalculation->calculateIndependentPrice(
                    $transport,
                    $transportInputPrice->getCurrency()
                );

                $newInputPrice = $this->inputPriceCalculation->getInputPrice(
                    $toInputPriceType,
                    $transportPrice->getPriceWithVat(),
                    $transport->getVat()->getPercent()
                );

                $transportInputPrice->setPrice($newInputPrice);
            }
        });
    }

    private function batchProcessQuery(Query $query, Closure $callback): void
    {
        $iteration = 0;

        foreach ($query->iterate() as $row) {
            $iteration++;
            $object = $row[0];

            $callback($object);

            if (($iteration % self::BATCH_SIZE) == 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();
    }
}
