<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;

class PaymentFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentRepository
     */
    protected $paymentRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportRepository
     */
    protected $transportRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentVisibilityCalculation
     */
    protected $paymentVisibilityCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    protected $paymentPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFactoryInterface
     */
    protected $paymentFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface
     */
    protected $paymentPriceFactory;

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
        EntityManagerInterface $em,
        PaymentRepository $paymentRepository,
        TransportRepository $transportRepository,
        PaymentVisibilityCalculation $paymentVisibilityCalculation,
        Domain $domain,
        ImageFacade $imageFacade,
        CurrencyFacade $currencyFacade,
        PaymentPriceCalculation $paymentPriceCalculation,
        PaymentFactoryInterface $paymentFactory,
        PaymentPriceFactoryInterface $paymentPriceFactory
    ) {
        $this->em = $em;
        $this->paymentRepository = $paymentRepository;
        $this->transportRepository = $transportRepository;
        $this->paymentVisibilityCalculation = $paymentVisibilityCalculation;
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
        $this->currencyFacade = $currencyFacade;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->paymentFactory = $paymentFactory;
        $this->paymentPriceFactory = $paymentPriceFactory;
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
        $this->updatePaymentPricesAndVats($payment, $paymentData);
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
        $this->updatePaymentPricesAndVats($payment, $paymentData);
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
        return $this->getVisibleByDomainId($this->domain->getId());
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
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    protected function updatePaymentPricesAndVats(Payment $payment, PaymentData $paymentData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

            $payment->setPriceAndVatByCurrencyAndDomainId(
                $this->paymentPriceFactory,
                $currency,
                $paymentData->pricesIndexedByDomainId[$domainId],
                $paymentData->vatsIndexedByDomainId[$domainId],
                $domainId
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
    public function getPaymentPricesWithVatByCurrencyAndDomainIdIndexedByPaymentId(Currency $currency, int $domainId): array
    {
        $paymentPricesWithVatByPaymentId = [];
        $payments = $this->getAllIncludingDeleted();
        foreach ($payments as $payment) {
            $paymentPrice = $this->paymentPriceCalculation->calculateIndependentPriceByCurrencyAndDomainId($payment, $currency, $domainId);
            $paymentPricesWithVatByPaymentId[$payment->getId()] = $paymentPrice->getPriceWithVat();
        }

        return $paymentPricesWithVatByPaymentId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param int $domainId
     * @return string[]
     */
    public function getPaymentVatPercentsByCurrencyAndDomainIdIndexedByPaymentId(Currency $currency, int $domainId): array
    {
        $paymentVatPercentsByPaymentId = [];
        $payments = $this->getAllIncludingDeleted();
        foreach ($payments as $payment) {
            $paymentVatPercentsByPaymentId[$payment->getId()] = $payment->getVatByCurrencyAndDomainId($currency, $domainId)->getPercent();
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[][]
     */
    public function getIndependentBasePricesIndexedByDomainIdAndCurrencyId(Payment $payment): array
    {
        $prices = [];
        foreach ($payment->getPrices() as $paymentInputPrice) {
            $domainId = $paymentInputPrice->getDomainId();
            $currency = $paymentInputPrice->getCurrency();
            $prices[$domainId][$currency->getId()] = $this->paymentPriceCalculation->calculateIndependentPriceByCurrencyAndDomainId($payment, $currency, $domainId);
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
            $prices[$domainId] = $this->paymentPriceCalculation->calculateIndependentPriceByCurrencyAndDomainId($payment, $currency, $domainId);
        }

        return $prices;
    }
}
