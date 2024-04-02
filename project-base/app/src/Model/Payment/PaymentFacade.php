<?php

declare(strict_types=1);

namespace App\Model\Payment;

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
 * @method \App\Model\Payment\Payment[] getVisibleOnCurrentDomainByTransport(\App\Model\Transport\Transport $transport)
 * @method \App\Model\Payment\Payment[] getVisibleForOrder(\App\Model\Order\Order $order)
 * @method \App\Model\Payment\Payment[] getVisibleOnDomainByTransport(int $domainId, \App\Model\Transport\Transport $transport)
 * @method bool isPaymentVisibleAndEnabledOnCurrentDomain(\App\Model\Payment\Payment $payment)
 */
class PaymentFacade extends BasePaymentFacade
{
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
}
