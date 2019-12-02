<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use BadMethodCallException;
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
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null $imageFacade
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        VatFacade $vatFacade,
        Domain $domain,
        ?ImageFacade $imageFacade = null
    ) {
        $this->paymentFacade = $paymentFacade;
        $this->vatFacade = $vatFacade;
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function setImageFacade(ImageFacade $imageFacade): void
    {
        if ($this->imageFacade !== null && $this->imageFacade !== $imageFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->imageFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->imageFacade = $imageFacade;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentData
     */
    public function create(): PaymentData
    {
        $paymentData = new PaymentData();
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
        $paymentData = new PaymentData();
        $this->fillFromPayment($paymentData, $payment);

        return $paymentData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     */
    protected function fillFromPayment(PaymentData $paymentData, Payment $payment)
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
