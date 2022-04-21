<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Transport;

use App\FrontendApi\Model\Transport\Exception\TransportPriceChangedException;
use App\FrontendApi\Model\Transport\Exception\TransportWeightLimitExceededException;
use App\Model\Cart\Cart;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Store\StoreFacade;
use App\Model\Transport\Transport;
use Shopsys\Cdn\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class TransportValidationFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private CurrencyFacade $currencyFacade;

    /**
     * @var \App\Model\Order\Preview\OrderPreviewFactory
     */
    private OrderPreviewFactory $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    private TransportPriceCalculation $transportPriceCalculation;

    /**
     * @var \App\Model\Store\StoreFacade
     */
    private StoreFacade $storeFacade;

    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        StoreFacade $storeFacade,
        Domain $domain,
        CurrencyFacade $currencyFacade,
        OrderPreviewFactory $orderPreviewFactory,
        TransportPriceCalculation $transportPriceCalculation,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->storeFacade = $storeFacade;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    public function checkPersonalPickupStoreAvailability(Transport $transport, ?string $pickupPlaceIdentifier): void
    {
        if ($pickupPlaceIdentifier === null || $transport->isPacketery()) {
            return;
        }

        $this->storeFacade->getByUuidEnabledOnDomain(
            $pickupPlaceIdentifier,
            $this->domain->getId()
        );
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     */
    public function checkTransportWeightLimit(Transport $transport, Cart $cart): void
    {
        if ($transport->getMaxWeight() !== null && $transport->getMaxWeight() < $cart->getTotalWeight()) {
            throw new TransportWeightLimitExceededException();
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     */
    public function checkTransportPrice(Transport $transport, Cart $cart): void
    {
        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        /** @var \App\Model\Customer\User\CustomerUser $currentCustomerUser */
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $transport,
            null, // payment is irrelevant here, however, it can be taken from the cart after https://shopsys.atlassian.net/browse/FWCC-847 is implemented
            $currentCustomerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode()
        );

        $calculatedTransportPrice = $this->transportPriceCalculation->calculatePrice(
            $transport,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId
        );

        $transportWatchedPrice = $cart->getTransportWatchedPrice();
        if ($transportWatchedPrice === null || !$calculatedTransportPrice->getPriceWithVat()->equals($transportWatchedPrice)) {
            throw new TransportPriceChangedException($calculatedTransportPrice);
        }
    }
}
