<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\Model\Cart\CartFacade;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use App\Model\Transport\Transport;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade as BasePaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository;
use Shopsys\FrameworkBundle\Model\Payment\PaymentVisibilityCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository;

/**
 * @property \App\Model\Payment\PaymentRepository $paymentRepository
<<<<<<< HEAD
=======
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Payment\PaymentRepository $paymentRepository, \App\Model\Transport\TransportRepository $transportRepository, \Shopsys\FrameworkBundle\Model\Payment\PaymentVisibilityCalculation $paymentVisibilityCalculation, \App\Component\Domain\Domain $domain, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation, \Shopsys\FrameworkBundle\Model\Payment\PaymentFactoryInterface $paymentFactory, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceFactoryInterface $paymentPriceFactory)
>>>>>>> b565de401... SD-1451 orders & customers bridge export stub
 * @method \App\Model\Payment\Payment create(\App\Model\Payment\PaymentData $paymentData)
 * @method edit(\App\Model\Payment\Payment $payment, \App\Model\Payment\PaymentData $paymentData)
 * @method \App\Model\Payment\Payment getById(int $id)
 * @method setAdditionalDataAndFlush(\App\Model\Payment\Payment $payment, \App\Model\Payment\PaymentData $paymentData)
 * @method \App\Model\Payment\Payment[] getVisibleOnCurrentDomain()
 * @method \App\Model\Payment\Payment[] getVisibleByDomainId(int $domainId)
 * @method updatePaymentPrices(\App\Model\Payment\Payment $payment, \Shopsys\FrameworkBundle\Component\Money\Money[] $pricesIndexedByDomainId, \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[] $vatsIndexedByDomainId)
 * @method \App\Model\Payment\Payment[] getAllIncludingDeleted()
 * @method \App\Model\Payment\Payment[] getAll()
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] getIndependentBasePricesIndexedByDomainId(\App\Model\Payment\Payment $payment)
 * @method \Shopsys\FrameworkBundle\Model\Pricing\Price[] getPricesIndexedByDomainId(\App\Model\Payment\Payment|null $payment)
 * @method \App\Model\Payment\Payment getByUuid(string $uuid)
 * @property \App\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \App\Model\Transport\TransportRepository $transportRepository
 */
class PaymentFacade extends BasePaymentFacade
{
    /**
     * @var CartFacade
     */
    private $cartFacade;

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
        PaymentPriceFactoryInterface $paymentPriceFactory,
        CartFacade $cartFacade
    ) {
        parent::__construct(
            $em,
            $paymentRepository,
            $transportRepository,
            $paymentVisibilityCalculation,
            $domain,
            $imageFacade,
            $currencyFacade,
            $paymentPriceCalculation,
            $paymentFactory,
            $paymentPriceFactory
        );

        $this->cartFacade = $cartFacade;
    }

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

<<<<<<< HEAD
    /**
     * @param \App\Model\Payment\Payment[] $payments
     * @return \App\Model\Payment\Payment[]
     */
    public function filterAllowedPaymentsForCurrentCart(array $payments): array
    {
        $isOverLimitPayment = $this->cartFacade->isCartContainsProductWithOverLimitQuantity();
        $allowedPayments = [];
        foreach ($payments as $payment) {
            if ($isOverLimitPayment === $payment->isOverLimitPayment()) {
                $allowedPayments[] = $payment;
            }
        }

        return $allowedPayments;
=======
    public function findByExternalId(int $id): ?Payment
    {
        return $this->paymentRepository->findByExternalId($id);
>>>>>>> b565de401... SD-1451 orders & customers bridge export stub
    }
}
