<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class PaymentDataFactory
{
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    protected function createInstance(): PaymentData
    {
        return new PaymentData();
    }

    public function create(): PaymentData
    {
        $paymentData = $this->createInstance();
        $this->fillNew($paymentData);

        return $paymentData;
    }

    protected function fillNew(PaymentData $paymentData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $paymentData->enabled[$domainId] = false;
            $paymentData->pricesIndexedByDomainId[$domainId] = Money::zero();
            $paymentData->vatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
            $paymentData->goPayPaymentMethodByDomainId[$domainId] = null;
            $paymentData->hiddenByGoPay[$domainId] = false;
            $paymentData->accountNumberByDomainId[$domainId] = null;
            $paymentData->ibanByDomainId[$domainId] = null;
            $paymentData->bicSwiftByDomainId[$domainId] = null;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $paymentData->name[$locale] = null;
            $paymentData->description[$locale] = null;
            $paymentData->instructions[$locale] = null;
        }

        $paymentData->image = $this->imageUploadDataFactory->create();
    }

    public function createFromPayment(Payment $payment): PaymentData
    {
        $paymentData = $this->createInstance();
        $this->fillFromPayment($paymentData, $payment);

        return $paymentData;
    }

    protected function fillFromPayment(PaymentData $paymentData, Payment $payment): void
    {
        $paymentData->hidden = $payment->isHidden();
        $paymentData->czkRounding = $payment->isCzkRounding();
        $paymentData->transports = $payment->getTransports();

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
            $paymentData->goPayPaymentMethodByDomainId[$domainId] = $payment->getGoPayPaymentMethodByDomainId($domainId);
            $paymentData->hiddenByGoPay[$domainId] = $payment->isHiddenByGoPayByDomainId($domainId);
            $paymentData->accountNumberByDomainId[$domainId] = $payment->getAccountNumber($domainId);
            $paymentData->ibanByDomainId[$domainId] = $payment->getIban($domainId);
            $paymentData->bicSwiftByDomainId[$domainId] = $payment->getBicSwift($domainId);
        }

        $paymentData->image = $this->imageUploadDataFactory->createFromEntityAndType($payment);
        $paymentData->type = $payment->getType();
    }
}
