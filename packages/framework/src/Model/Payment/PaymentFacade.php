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
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDomainFactoryInterface
     */
    protected $paymentDomainFactory;

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
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentDomainFactoryInterface $paymentDomainFactory
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
        PaymentDomainFactoryInterface $paymentDomainFactory,
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
        $this->paymentDomainFactory = $paymentDomainFactory;
        $this->paymentPriceFactory = $paymentPriceFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentEditData $paymentEditData
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function create(PaymentEditData $paymentEditData)
    {
        $payment = $this->paymentFactory->create($paymentEditData->paymentData);
        $this->em->persist($payment);
        $this->em->flush();
        $this->updatePaymentPrices($payment, $paymentEditData->pricesByCurrencyId);
        $this->createPaymentDomains($payment, $paymentEditData->paymentData->domains);
        $this->setAdditionalDataAndFlush($payment, $paymentEditData->paymentData);

        return $payment;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentEditData $paymentEditData
     */
    public function edit(Payment $payment, PaymentEditData $paymentEditData)
    {
        $payment->edit($paymentEditData->paymentData);
        $this->updatePaymentPrices($payment, $paymentEditData->pricesByCurrencyId);
        $this->deletePaymentDomainsByPayment($payment);
        $this->createPaymentDomains($payment, $paymentEditData->paymentData->domains);
        $this->setAdditionalDataAndFlush($payment, $paymentEditData->paymentData);
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
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentDomain[]
     */
    public function getPaymentDomainsByPayment(Payment $payment)
    {
        return $this->paymentRepository->getPaymentDomainsByPayment($payment);
    }

    /**
     * @param int $id
     */
    public function deleteById($id)
    {
        $payment = $this->getById($id);
        $payment->markAsDeleted();
        $this->deletePaymentDomainsByPayment($payment);
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
        $this->imageFacade->uploadImage($payment, $paymentData->image->uploadedFiles, null);
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
     * @param array $domainIds
     */
    protected function createPaymentDomains(Payment $payment, array $domainIds)
    {
        foreach ($domainIds as $domainId) {
            $paymentDomain = $this->paymentDomainFactory->create($payment, $domainId);
            $this->em->persist($paymentDomain);
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     */
    protected function deletePaymentDomainsByPayment(Payment $payment)
    {
        $paymentDomains = $this->getPaymentDomainsByPayment($payment);
        foreach ($paymentDomains as $paymentDomain) {
            $this->em->remove($paymentDomain);
        }
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param string[] $pricesByCurrencyId
     */
    protected function updatePaymentPrices(Payment $payment, $pricesByCurrencyId)
    {
        foreach ($this->currencyFacade->getAll() as $currency) {
            $price = $pricesByCurrencyId[$currency->getId()];
            $payment->setPrice($this->paymentPriceFactory, $currency, $price);
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
     * @return string[]
     */
    public function getPaymentPricesWithVatIndexedByPaymentId(Currency $currency)
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
    public function getPaymentVatPercentsIndexedByPaymentId()
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
    public function getAll()
    {
        return $this->paymentRepository->getAll();
    }
}
