<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;

class PaymentFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository $paymentRepository
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportRepository $transportRepository
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentVisibilityCalculation $paymentVisibilityCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFactoryInterface $paymentFactory
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface $paymentPriceFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PaymentRepository $paymentRepository,
        protected readonly TransportRepository $transportRepository,
        protected readonly PaymentVisibilityCalculation $paymentVisibilityCalculation,
        protected readonly Domain $domain,
        protected readonly ImageFacade $imageFacade,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly PaymentFactoryInterface $paymentFactory,
        protected readonly PaymentPriceFactoryInterface $paymentPriceFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function create(PaymentData $paymentData)
    {
        $payment = $this->paymentFactory->create($paymentData);
        $this->em->persist($payment);
        $this->em->flush();
        $this->updatePaymentPrices(
            $payment,
            $paymentData->pricesIndexedByDomainId,
            $paymentData->vatsIndexedByDomainId,
        );
        $this->setAdditionalDataAndFlush($payment, $paymentData);

        return $payment;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    public function edit(Payment $payment, PaymentData $paymentData)
    {
        $payment->edit($paymentData);
        $this->updatePaymentPrices(
            $payment,
            $paymentData->pricesIndexedByDomainId,
            $paymentData->vatsIndexedByDomainId,
        );
        $this->setAdditionalDataAndFlush($payment, $paymentData);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getById($id)
    {
        return $this->paymentRepository->getById($id);
    }

    /**
     * @param int $id
     */
    public function deleteById($id)
    {
        $payment = $this->getById($id);
        $payment->markAsDeleted();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    protected function setAdditionalDataAndFlush(Payment $payment, PaymentData $paymentData)
    {
        $transports = $this->transportRepository->getAllByIds($paymentData->transports);
        $payment->setTransports($transports);
        $this->imageFacade->manageImages($payment, $paymentData->image);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getVisibleOnCurrentDomain()
    {
        $allPayments = $this->paymentRepository->getAllWithEagerLoadedDomainsAndTranslations($this->domain->getCurrentDomainConfig());

        return $this->paymentVisibilityCalculation->filterVisible($allPayments, $this->domain->getId());
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getVisibleByDomainId($domainId)
    {
        $allPayments = $this->paymentRepository->getAll();

        return $this->paymentVisibilityCalculation->filterVisible($allPayments, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Component\Money\Money[] $pricesIndexedByDomainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[] $vatsIndexedByDomainId
     */
    protected function updatePaymentPrices(
        Payment $payment,
        array $pricesIndexedByDomainId,
        array $vatsIndexedByDomainId,
    ): void {
        foreach ($this->domain->getAllIds() as $domainId) {
            $existPriceForDomain = $payment->hasPriceForDomain($domainId);
            $payment->setPrice($pricesIndexedByDomainId[$domainId], $domainId);

            if ($existPriceForDomain !== false) {
                continue;
            }

            $payment->addPrice(
                $this->paymentPriceFactory->create($payment, $pricesIndexedByDomainId[$domainId], $domainId),
            );
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAllIncludingDeleted()
    {
        return $this->paymentRepository->getAllIncludingDeleted();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money[]
     */
    public function getPaymentPricesWithVatByCurrencyAndDomainIdIndexedByPaymentId(
        Currency $currency,
        int $domainId,
    ): array {
        $paymentPricesWithVatByPaymentId = [];
        $payments = $this->getAllIncludingDeleted();

        foreach ($payments as $payment) {
            $paymentPrice = $this->paymentPriceCalculation->calculateIndependentPrice($payment, $currency, $domainId);
            $paymentPricesWithVatByPaymentId[$payment->getId()] = $paymentPrice->getPriceWithVat();
        }

        return $paymentPricesWithVatByPaymentId;
    }

    /**
     * @param int $domainId
     * @return string[]
     */
    public function getPaymentVatPercentsByDomainIdIndexedByPaymentId(int $domainId): array
    {
        $paymentVatPercentsByPaymentId = [];
        $payments = $this->getAllIncludingDeleted();

        foreach ($payments as $payment) {
            $paymentVatPercentsByPaymentId[$payment->getId()] = $payment->getPaymentDomain(
                $domainId,
            )->getVat()->getPercent();
        }

        return $paymentVatPercentsByPaymentId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAll()
    {
        return $this->paymentRepository->getAll();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getIndependentBasePricesIndexedByDomainId(Payment $payment): array
    {
        $prices = [];

        foreach ($payment->getPrices() as $paymentInputPrice) {
            $domainId = $paymentInputPrice->getDomainId();
            $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
            $prices[$domainId] = $this->paymentPriceCalculation->calculateIndependentPrice(
                $payment,
                $currency,
                $domainId,
            );
        }

        return $prices;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment|null $payment
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getPricesIndexedByDomainId(?Payment $payment): array
    {
        $prices = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

            if ($payment === null) {
                $prices[$domainId] = Price::zero();

                continue;
            }

            $prices[$domainId] = $this->paymentPriceCalculation->calculateIndependentPrice(
                $payment,
                $currency,
                $domainId,
            );
        }

        return $prices;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getByUuid(string $uuid): Payment
    {
        return $this->paymentRepository->getOneByUuid($uuid);
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getEnabledOnDomainByUuid(string $uuid, int $domainId): Payment
    {
        return $this->paymentRepository->getEnabledOnDomainByUuid($uuid, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getVisibleForOrder(Order $order): array
    {
        return $this->getVisibleOnDomainByTransport($order->getDomainId(), $order->getTransport());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getVisibleOnCurrentDomainByTransport(Transport $transport): array
    {
        return $this->getVisibleOnDomainByTransport($this->domain->getId(), $transport);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    protected function getVisibleOnDomainByTransport(int $domainId, Transport $transport): array
    {
        $paymentsByTransport = $this->paymentRepository->getAllByTransport($transport);

        return $this->paymentVisibilityCalculation->filterVisible($paymentsByTransport, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param int $domainId
     */
    public function hideByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod, int $domainId): void
    {
        $payments = $this->paymentRepository->getByGoPayPaymentMethod($goPayPaymentMethod, $domainId);

        foreach ($payments as $payment) {
            $payment->hideByGoPayOnDomain($domainId);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param int $domainId
     */
    public function unHideByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod, int $domainId): void
    {
        $payments = $this->paymentRepository->getByGoPayPaymentMethod($goPayPaymentMethod, $domainId);

        foreach ($payments as $payment) {
            $payment->unHideByGoPayOnDomain($domainId);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return bool
     */
    public function isPaymentVisibleAndEnabledOnCurrentDomain(Payment $payment): bool
    {
        try {
            $domainId = $this->domain->getId();
            $payment = $this->getEnabledOnDomainByUuid($payment->getUuid(), $domainId);

            return $this->paymentVisibilityCalculation->isVisible($payment, $domainId);
        } catch (PaymentNotFoundException $exception) {
            return false;
        }
    }
}
