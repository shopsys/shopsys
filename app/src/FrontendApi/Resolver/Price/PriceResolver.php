<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Price;

use App\FrontendApi\Model\Cart\CartFacade;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Product\Product;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrontendApiBundle\Model\Price\PriceFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Price\PriceResolver as BasePriceResolver;

/**
 * @property \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
 */
class PriceResolver extends BasePriceResolver
{
    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\Model\Order\Preview\OrderPreviewFactory
     */
    private OrderPreviewFactory $orderPreviewFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrontendApiBundle\Model\Price\PriceFacade $priceFacade
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     */
    public function __construct(
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        PaymentPriceCalculation $paymentPriceCalculation,
        Domain $domain,
        CurrencyFacade $currencyFacade,
        TransportPriceCalculation $transportPriceCalculation,
        PriceFacade $priceFacade,
        CartFacade $cartFacade,
        CurrentCustomerUser $currentCustomerUser,
        OrderPreviewFactory $orderPreviewFactory
    ) {
        parent::__construct($productCachedAttributesFacade, $productOnCurrentDomainFacade, $paymentPriceCalculation, $domain, $currencyFacade, $transportPriceCalculation, $priceFacade);

        $this->cartFacade = $cartFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->orderPreviewFactory = $orderPreviewFactory;
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $cartUuid
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function resolveByTransport(Transport $transport, ?string $cartUuid = null): Price
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        if ($customerUser === null && $cartUuid === null) {
            return parent::resolveByTransport($transport);
        }

        $cart = $this->cartFacade->findCart($customerUser, $cartUuid);
        if ($cart === null) {
            return parent::resolveByTransport($transport);
        }

        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $transport,
            null,
            $customerUser
        );

        return $this->transportPriceCalculation->calculatePrice(
            $transport,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId
        );
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @param string|null $cartUuid
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function resolveByPayment(Payment $payment, ?string $cartUuid = null): Price
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        if ($customerUser === null && $cartUuid === null) {
            return parent::resolveByPayment($payment);
        }

        $cart = $this->cartFacade->findCart($customerUser, $cartUuid);
        if ($cart === null) {
            return parent::resolveByPayment($payment);
        }

        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            null,
            $payment,
            $customerUser
        );

        return $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId
        );
    }

    /**
     * {@inheritDoc}
     */
    public function resolveByProduct($data): ProductPrice
    {
        if ($data instanceof Product) {
            $productPrice = $this->productCachedAttributesFacade->getProductSellingPrice($data);
        } else {
            $productPrice = $this->priceFacade->createProductPriceFromArrayForCurrentCustomer($data['prices']);
        }

        if ($productPrice === null) {
            throw new UserError('The product price is not set.');
        }

        return $productPrice;
    }

    /**
     * {@inheritdoc}
     */
    public static function getAliases(): array
    {
        // parent not called intentionally, see https://github.com/shopsys/shopsys/issues/2384
        return [
            'resolveByProduct' => 'productPriceResolver',
        ];
    }
}
