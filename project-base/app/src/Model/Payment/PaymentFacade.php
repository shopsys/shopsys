<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade as BasePaymentFacade;

/**
 * @property \App\Model\Payment\PaymentRepository $paymentRepository
 * @method \App\Model\Payment\Payment create(\App\Model\Payment\PaymentData $paymentData)
 * @method edit(\App\Model\Payment\Payment $payment, \App\Model\Payment\PaymentData $paymentData)
 * @method \App\Model\Payment\Payment getById(int $id)
 * @method setAdditionalDataAndFlush(\App\Model\Payment\Payment $payment, \App\Model\Payment\PaymentData $paymentData)
 * @method \App\Model\Payment\Payment[] getVisibleByDomainId(int $domainId)
 * @method updatePaymentPrices(\App\Model\Payment\Payment $payment, \Shopsys\FrameworkBundle\Component\Money\Money[] $pricesIndexedByDomainId, \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[] $vatsIndexedByDomainId)
 * @method \App\Model\Payment\Payment[] getAllIncludingDeleted()
 * @method \App\Model\Payment\Payment[] getAll()
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] getIndependentBasePricesIndexedByDomainId(\App\Model\Payment\Payment $payment)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] getPricesIndexedByDomainId(\App\Model\Payment\Payment|null $payment)
 * @method \App\Model\Payment\Payment getByUuid(string $uuid)
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \App\Model\Transport\TransportRepository $transportRepository
 * @method \App\Model\Payment\Payment getEnabledOnDomainByUuid(string $uuid, int $domainId)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Payment\PaymentRepository $paymentRepository, \App\Model\Transport\TransportRepository $transportRepository, \Shopsys\FrameworkBundle\Model\Payment\PaymentVisibilityCalculation $paymentVisibilityCalculation, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation, \Shopsys\FrameworkBundle\Model\Payment\PaymentFactoryInterface $paymentFactory, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface $paymentPriceFactory)
 */
class PaymentFacade extends BasePaymentFacade
{
    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     */
    public function hideByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod): void
    {
        $payments = $this->paymentRepository->getByGoPayPaymentMethod($goPayPaymentMethod);

        foreach ($payments as $payment) {
            $payment->hideByGoPay();
        }

        $this->em->flush();
    }

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     */
    public function unHideByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod): void
    {
        $payments = $this->paymentRepository->getByGoPayPaymentMethod($goPayPaymentMethod);

        foreach ($payments as $payment) {
            $payment->unHideByGoPay();
        }

        $this->em->flush();
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @return \App\Model\Payment\Payment[]
     */
    public function getVisibleOnCurrentDomainByTransport(Transport $transport): array
    {
        $paymentsByTransport = $this->paymentRepository->getAllByTransport($transport);
        /** @var \App\Model\Payment\Payment[] $payments */
        $payments = $this->paymentVisibilityCalculation->filterVisible($paymentsByTransport, $this->domain->getId());

        return $payments;
    }

    /**
     * @return \App\Model\Payment\Payment[]
     */
    public function getVisibleOnCurrentDomain()
    {
        $allPayments = $this->paymentRepository->getAllWithEagerLoadedDomainsAndTranslations($this->domain->getCurrentDomainConfig());

        /** @var \App\Model\Payment\Payment[] $visiblePayments */
        $visiblePayments = $this->paymentVisibilityCalculation->filterVisible($allPayments, $this->domain->getId());

        return $visiblePayments;
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @return bool
     */
    public function isPaymentVisibleAndEnabledOnCurrentDomain(Payment $payment): bool
    {
        try {
            $this->getEnabledOnDomainByUuid($payment->getUuid(), $this->domain->getId());
        } catch (PaymentNotFoundException $exception) {
            return false;
        }

        return true;
    }
}
