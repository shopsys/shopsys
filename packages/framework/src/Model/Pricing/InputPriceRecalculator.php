<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class InputPriceRecalculator
{
    protected const BATCH_SIZE = 500;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly InputPriceCalculation $inputPriceCalculation,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    public function recalculateToInputPricesWithoutVat(): void
    {
        $this->recalculateInputPriceForNewType(PricingSetting::PRICE_TYPE_WITHOUT_VAT);
    }

    public function recalculateToInputPricesWithVat(): void
    {
        $this->recalculateInputPriceForNewType(PricingSetting::PRICE_TYPE_WITH_VAT);
    }

    protected function recalculateInputPriceForNewType(int $newInputPriceType): void
    {
        $this->recalculateTransportsInputPriceForNewType($newInputPriceType);
        $this->recalculatePaymentsInputPriceForNewType($newInputPriceType);
    }

    protected function recalculatePaymentsInputPriceForNewType(int $toInputPriceType): void
    {
        $query = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Payment::class, 'p')
            ->getQuery();

        $this->batchProcessQuery($query, function (Payment $payment) use ($toInputPriceType): void {
            foreach ($payment->getPrices() as $paymentInputPrice) {
                $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($paymentInputPrice->getDomainId());
                $paymentPrice = $this->paymentPriceCalculation->calculateIndependentPrice(
                    $payment,
                    $paymentInputPrice->getDomainId(),
                    $currency->getRoundingType(),
                    $currency->getRoundingPlacesPriceWithoutVat(),
                );

                $newInputPrice = $this->inputPriceCalculation->getInputPrice(
                    $toInputPriceType,
                    $paymentPrice->getPriceWithVat(),
                    $payment->getPaymentDomain($paymentInputPrice->getDomainId())->getVat()->getPercent(),
                );

                $paymentInputPrice->setPrice($newInputPrice);
            }
        });
    }

    protected function recalculateTransportsInputPriceForNewType(int $toInputPriceType): void
    {
        $query = $this->em->createQueryBuilder()
            ->select('t')
            ->from(Transport::class, 't')
            ->getQuery();

        $this->batchProcessQuery($query, function (Transport $transport) use ($toInputPriceType): void {
            foreach ($transport->getPrices() as $transportInputPrice) {
                $transportPrice = $this->transportPriceCalculation->calculateIndependentPrice(
                    $transportInputPrice,
                );

                $newInputPrice = $this->inputPriceCalculation->getInputPrice(
                    $toInputPriceType,
                    $transportPrice->getPriceWithVat(),
                    $transport->getTransportDomain($transportInputPrice->getDomainId())->getVat()->getPercent(),
                );

                $transportInputPrice->setPrice($newInputPrice);
            }
        });
    }

    protected function batchProcessQuery(Query $query, Closure $callback): void
    {
        $iteration = 0;

        foreach ($query->toIterable() as $object) {
            $iteration++;

            $callback($object);

            if ($iteration % static::BATCH_SIZE === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();
    }
}
