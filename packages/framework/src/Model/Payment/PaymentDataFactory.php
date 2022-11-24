<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class PaymentDataFactory implements PaymentDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    protected $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    protected $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        VatFacade $vatFacade,
        Domain $domain,
        ImageFacade $imageFacade
    ) {
        $this->paymentFacade = $paymentFacade;
        $this->vatFacade = $vatFacade;
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
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

        $paymentData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($payment, null);
    }
}
