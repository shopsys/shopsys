<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class PaymentDataFactory implements PaymentDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentData
     */
    protected function createInstance(): PaymentData
    {
        return new PaymentData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentData
     */
    public function create(): PaymentData
    {
        $paymentData = $this->createInstance();
        $this->fillNew($paymentData);

        return $paymentData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     */
    protected function fillNew(PaymentData $paymentData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $paymentData->enabled[$domainId] = true;
            $paymentData->pricesIndexedByDomainId[$domainId] = Money::zero();
            $paymentData->vatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = null;
            $paymentData->description[$locale] = null;
            $paymentData->instructions[$locale] = null;
        }
        $paymentData->image = $this->imageUploadDataFactory->create();
        $paymentData->hiddenByGoPay = false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentData
     */
    public function createFromPayment(Payment $payment): PaymentData
    {
        $paymentData = $this->createInstance();
        $this->fillFromPayment($paymentData, $payment);

        return $paymentData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     */
    protected function fillFromPayment(PaymentData $paymentData, Payment $payment): void
    {
        $paymentData->hidden = $payment->isHidden();
        $paymentData->czkRounding = $payment->isCzkRounding();
        $paymentData->transports = $payment->getTransports();

        /** @var \Shopsys\FrameworkBundle\Model\Payment\PaymentTranslation[] $translations */
        $translations = $payment->getTranslations();

        $names = [];
        $descriptions = [];
        $instructions = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
            $descriptions[$translate->getLocale()] = $translate->getDescription();
            $instructions[$translate->getLocale()] = $translate->getInstructions();
        }

        $paymentData->name = $names;
        $paymentData->description = $descriptions;
        $paymentData->instructions = $instructions;

        foreach ($this->domain->getAllIds() as $domainId) {
            $paymentData->enabled[$domainId] = $payment->isEnabled($domainId);
            $paymentData->pricesIndexedByDomainId[$domainId] = $payment->getPrice($domainId)->getPrice();
            $paymentData->vatsIndexedByDomainId[$domainId] = $payment->getPaymentDomain($domainId)->getVat();
        }

        $paymentData->image = $this->imageUploadDataFactory->createFromEntityAndType($payment);
        $paymentData->type = $payment->getType();
        $paymentData->goPayPaymentMethod = $payment->getGoPayPaymentMethod();
        $paymentData->hiddenByGoPay = $payment->isHiddenByGoPay();
    }
}
