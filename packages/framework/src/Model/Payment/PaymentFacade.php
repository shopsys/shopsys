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
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PaymentRepository $paymentRepository,
        protected readonly TransportRepository $transportRepository,
        protected readonly PaymentVisibilityCalculation $paymentVisibilityCalculation,
        protected readonly Domain $domain,
        protected readonly ImageFacade $imageFacade,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly PaymentFactory $paymentFactory,
        protected readonly PaymentPriceFactory $paymentPriceFactory,
        protected readonly OrderRoundingTypeEnum $orderRoundingTypeEnum,
    ) {
    }

    public function create(PaymentData $paymentData): Payment
    {
        $this->validateOrderRoundingTypes($paymentData);
        $payment = $this->paymentFactory->create($paymentData);
        $this->em->persist($payment);
        $this->em->flush();
        $this->updatePaymentPrices(
            $payment,
            $paymentData->pricesIndexedByDomainIdAndCurrencyCode,
            $paymentData->vatsIndexedByDomainId,
        );
        $this->setAdditionalDataAndFlush($payment, $paymentData);

        return $payment;
    }

    public function edit(Payment $payment, PaymentData $paymentData): void
    {
        $this->validateOrderRoundingTypes($paymentData);
        $payment->edit($paymentData);
        $this->updatePaymentPrices(
            $payment,
            $paymentData->pricesIndexedByDomainIdAndCurrencyCode,
            $paymentData->vatsIndexedByDomainId,
        );
        $this->setAdditionalDataAndFlush($payment, $paymentData);
    }

    public function getById(int $id): Payment
    {
        return $this->paymentRepository->getById($id);
    }

    public function deleteById(int $id): void
    {
        $payment = $this->getById($id);
        $payment->markAsDeleted();
        $this->em->flush();
    }

    protected function setAdditionalDataAndFlush(Payment $payment, PaymentData $paymentData): void
    {
        $transports = $this->transportRepository->getAllByIds($paymentData->transports);
        $payment->setTransports($transports);
        $this->imageFacade->manageImages($payment, $paymentData->image);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getVisibleOnCurrentDomain(): array
    {
        $allPayments = $this->paymentRepository->getAllWithEagerLoadedDomainsAndTranslations($this->domain->getCurrentDomainConfig());

        return $this->paymentVisibilityCalculation->filterVisible($allPayments, $this->domain->getId());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getVisibleByDomainId(int $domainId): array
    {
        $allPayments = $this->paymentRepository->getAll();

        return $this->paymentVisibilityCalculation->filterVisible($allPayments, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[] $vatsIndexedByDomainId
     */
    /**
     * @param array<int, array<string, \Shopsys\FrameworkBundle\Component\Money\Money>> $pricesIndexedByDomainIdAndCurrencyCode
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[] $vatsIndexedByDomainId
     */
    protected function updatePaymentPrices(
        Payment $payment,
        array $pricesIndexedByDomainIdAndCurrencyCode,
        array $vatsIndexedByDomainId,
    ): void {
        foreach ($this->domain->getAllIds() as $domainId) {
            foreach ($pricesIndexedByDomainIdAndCurrencyCode[$domainId] ?? [] as $currencyCode => $price) {
                $currency = $this->currencyFacade->getByCode($currencyCode);

                if ($payment->hasPriceForDomainAndCurrency($domainId, $currency)) {
                    $payment->setPrice($price, $domainId, $currency);

                    continue;
                }

                $payment->addPrice(
                    $this->paymentPriceFactory->create($payment, $price, $domainId, $currency),
                );
            }
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getAllIncludingDeleted(): array
    {
        return $this->paymentRepository->getAllIncludingDeleted();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money[]
     */
    public function getPaymentPricesWithVatByDomainIdIndexedByPaymentId(
        int $domainId,
        Currency $currency,
    ): array {
        $paymentPricesWithVatByPaymentId = [];
        $payments = $this->getAllIncludingDeleted();

        foreach ($payments as $payment) {
            $paymentPrice = $this->paymentPriceCalculation->calculateIndependentPrice($payment, $domainId, $currency);
            $paymentPricesWithVatByPaymentId[$payment->getId()] = $paymentPrice->getPriceWithVat();
        }

        return $paymentPricesWithVatByPaymentId;
    }

    /**
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
    public function getAll(): array
    {
        return $this->paymentRepository->getAll();
    }

    /**
     * @return int[]
     */
    public function getDomainIdsWithAnyEnabledPayment(): array
    {
        return $this->paymentRepository->getDomainIdsWithAnyEnabledPayment();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[]
     */
    /**
     * Only the domain default currency prices are returned (used by the admin grid)
     */
    public function getIndependentBasePricesIndexedByDomainId(Payment $payment): array
    {
        $prices = [];

        foreach ($payment->getPrices() as $paymentInputPrice) {
            $domainId = $paymentInputPrice->getDomainId();
            $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

            if ($paymentInputPrice->getCurrency() !== $defaultCurrency) {
                continue;
            }

            $prices[$domainId] = $this->paymentPriceCalculation->calculateIndependentPrice(
                $payment,
                $domainId,
                $defaultCurrency,
            );
        }

        return $prices;
    }

    /**
     * @return array<int, array<string, \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface>>
     */
    public function getPricesIndexedByDomainIdAndCurrencyCode(?Payment $payment): array
    {
        $prices = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            foreach ($this->currencyFacade->getEnabledCurrenciesByDomainId($domainId) as $currency) {
                if ($payment === null) {
                    $prices[$domainId][$currency->getCode()] = Price::zero();

                    continue;
                }

                $prices[$domainId][$currency->getCode()] = $this->paymentPriceCalculation->calculateIndependentPrice(
                    $payment,
                    $domainId,
                    $currency,
                );
            }
        }

        return $prices;
    }

    public function getByUuid(string $uuid): Payment
    {
        return $this->paymentRepository->getOneByUuid($uuid);
    }

    public function getEnabledOnDomainByUuid(string $uuid, int $domainId): Payment
    {
        return $this->paymentRepository->getEnabledOnDomainByUuid($uuid, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getVisibleForOrder(Order $order): array
    {
        return $this->getVisibleOnDomainByTransport($order->getDomainId(), $order->getTransport());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getVisibleOnCurrentDomainByTransport(Transport $transport): array
    {
        return $this->getVisibleOnDomainByTransport($this->domain->getId(), $transport);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    protected function getVisibleOnDomainByTransport(int $domainId, Transport $transport): array
    {
        $paymentsByTransport = $this->paymentRepository->getAllByTransport($transport);

        return $this->paymentVisibilityCalculation->filterVisible($paymentsByTransport, $domainId);
    }

    public function hideByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod, int $domainId): void
    {
        $payments = $this->paymentRepository->getByGoPayPaymentMethod($goPayPaymentMethod, $domainId);

        foreach ($payments as $payment) {
            $payment->hideByGoPayOnDomain($domainId);
        }

        $this->em->flush();
    }

    public function unHideByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod, int $domainId): void
    {
        $payments = $this->paymentRepository->getByGoPayPaymentMethod($goPayPaymentMethod, $domainId);

        foreach ($payments as $payment) {
            $payment->unHideByGoPayOnDomain($domainId);
        }

        $this->em->flush();
    }

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

    public function findPaymentByExternalMethodTransportAndDomainId(
        string $externalPaymentMethod,
        Transport $transport,
        int $domainId,
    ): ?Payment {
        return $this->paymentRepository->findPaymentByExternalMethodTransportAndDomainId(
            $externalPaymentMethod,
            $transport,
            $domainId,
        );
    }

    protected function validateOrderRoundingTypes(PaymentData $paymentData): void
    {
        foreach ($paymentData->orderRoundingTypeByDomainId as $orderRoundingType) {
            $this->orderRoundingTypeEnum->validateCase($orderRoundingType);
        }
    }
}
