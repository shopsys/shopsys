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

    public function create(PaymentData $paymentData): \Shopsys\FrameworkBundle\Model\Payment\Payment
    {
        $payment = $this->paymentFactory->create($paymentData);
        $this->em->persist($payment);
        $this->em->flush();
        $this->updatePaymentPrices($payment, $paymentData->pricesByCurrencyId);
        $this->setAdditionalDataAndFlush($payment, $paymentData);

        return $payment;
    }

    public function edit(Payment $payment, PaymentData $paymentData): void
    {
        $payment->edit($paymentData);
        $this->updatePaymentPrices($payment, $paymentData->pricesByCurrencyId);
        $this->setAdditionalDataAndFlush($payment, $paymentData);
    }

    public function getById(int $id): \Shopsys\FrameworkBundle\Model\Payment\Payment
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
        $this->imageFacade->uploadImage($payment, $paymentData->image->uploadedFiles, null);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public function getVisibleOnCurrentDomain(): array
    {
        return $this->getVisibleByDomainId($this->domain->getId());
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
     * @param string[] $pricesByCurrencyId
     */
    protected function updatePaymentPrices(Payment $payment, $pricesByCurrencyId): void
    {
        foreach ($this->currencyFacade->getAll() as $currency) {
            $price = $pricesByCurrencyId[$currency->getId()];
            $payment->setPrice($this->paymentPriceFactory, $currency, $price);
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
     * @return string[]
     */
    public function getPaymentPricesWithVatIndexedByPaymentId(Currency $currency): array
    {
        $paymentPricesWithVatByPaymentId = [];
        $payments = $this->getAllIncludingDeleted();
        foreach ($payments as $payment) {
            $paymentPrice = $this->paymentPriceCalculation->calculateIndependentPrice($payment, $currency);
            $paymentPricesWithVatByPaymentId[$payment->getId()] = $paymentPrice->getPriceWithVat();
        }

        return $paymentPricesWithVatByPaymentId;
    }

    /**
     * @return string[]
     */
    public function getPaymentVatPercentsIndexedByPaymentId(): array
    {
        $paymentVatPercentsByPaymentId = [];
        $payments = $this->getAllIncludingDeleted();
        foreach ($payments as $payment) {
            $paymentVatPercentsByPaymentId[$payment->getId()] = $payment->getVat()->getPercent();
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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getIndependentBasePricesIndexedByCurrencyId(Payment $payment): array
    {
        $prices = [];
        foreach ($payment->getPrices() as $paymentInputPrice) {
            $currency = $paymentInputPrice->getCurrency();
            $prices[$currency->getId()] = $this->paymentPriceCalculation->calculateIndependentPrice($payment, $currency);
        }
        return $prices;
    }
}
