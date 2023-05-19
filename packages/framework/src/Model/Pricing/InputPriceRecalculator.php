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

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Pricing\InputPriceCalculation $inputPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly InputPriceCalculation $inputPriceCalculation,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    public function recalculateToInputPricesWithoutVat()
    {
        $this->recalculateInputPriceForNewType(PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);
    }

    public function recalculateToInputPricesWithVat()
    {
        $this->recalculateInputPriceForNewType(PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);
    }

    /**
     * @param int $newInputPriceType
     */
    protected function recalculateInputPriceForNewType($newInputPriceType)
    {
        $this->recalculateTransportsInputPriceForNewType($newInputPriceType);
        $this->recalculatePaymentsInputPriceForNewType($newInputPriceType);
    }

    /**
     * @param int $toInputPriceType
     */
    protected function recalculatePaymentsInputPriceForNewType($toInputPriceType)
    {
        $query = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Payment::class, 'p')
            ->getQuery();

        $this->batchProcessQuery($query, function (Payment $payment) use ($toInputPriceType) {
            foreach ($payment->getPrices() as $paymentInputPrice) {
                $paymentPrice = $this->paymentPriceCalculation->calculateIndependentPrice(
                    $payment,
                    $this->currencyFacade->getDomainDefaultCurrencyByDomainId($paymentInputPrice->getDomainId()),
                    $paymentInputPrice->getDomainId(),
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

    /**
     * @param int $toInputPriceType
     */
    protected function recalculateTransportsInputPriceForNewType($toInputPriceType)
    {
        $query = $this->em->createQueryBuilder()
            ->select('t')
            ->from(Transport::class, 't')
            ->getQuery();

        $this->batchProcessQuery($query, function (Transport $transport) use ($toInputPriceType) {
            foreach ($transport->getPrices() as $transportInputPrice) {
                $defaultCurrencyForDomain = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(
                    $transportInputPrice->getDomainId(),
                );
                $transportPrice = $this->transportPriceCalculation->calculateIndependentPrice(
                    $transport,
                    $defaultCurrencyForDomain,
                    $transportInputPrice->getDomainId(),
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

    /**
     * @param \Doctrine\ORM\Query $query
     * @param \Closure $callback
     */
    protected function batchProcessQuery(Query $query, Closure $callback)
    {
        $iteration = 0;

        foreach ($query->iterate() as $row) {
            $iteration++;
            $object = $row[0];

            $callback($object);

            if ($iteration % static::BATCH_SIZE === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();
    }
}
