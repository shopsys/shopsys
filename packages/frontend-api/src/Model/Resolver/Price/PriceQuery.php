<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use ArrayObject;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrontendApiBundle\Component\GqlContext\GqlContextHelper;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Price\PriceFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PriceQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrontendApiBundle\Model\Price\PriceFacade $priceFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     */
    public function __construct(
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly PriceFacade $priceFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderPreviewFactory $orderPreviewFactory,
        protected readonly CartApiFacade $cartApiFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function priceByProductQuery($data): ProductPrice
    {
        if ($data instanceof Product) {
            return $this->productCachedAttributesFacade->getProductSellingPrice($data);
        }

        return $this->priceFacade->createProductPriceFromArrayForCurrentCustomer($data['prices']);
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @param string|null $cartUuid
     * @param \ArrayObject|null $context
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function priceByPaymentQuery(
        Payment $payment,
        ?string $cartUuid = null,
        ?ArrayObject $context = null,
    ): Price {
        $cartUuid = $cartUuid ?? GqlContextHelper::getCartUuid($context);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null && $cartUuid === null) {
            return $this->calculateIndependentPaymentPrice($payment);
        }

        $cart = $this->cartApiFacade->findCart($customerUser, $cartUuid);

        if ($cart === null) {
            return $this->calculateIndependentPaymentPrice($payment);
        }

        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            null,
            $payment,
            $customerUser,
        );

        return $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected function calculateIndependentPaymentPrice(Payment $payment): Price
    {
        return $this->paymentPriceCalculation->calculateIndependentPrice(
            $payment,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function priceByTransportQuery(Transport $transport): Price
    {
        return $this->transportPriceCalculation->calculateIndependentPrice(
            $transport,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
        );
    }
}
