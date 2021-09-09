<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeFacade;
use App\Model\Order\PromoCode\PromoCodeLimitResolver;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade as BasePlaceOrderFacade;

/**
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @property \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
 * @method \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview createOrderPreview(\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, \App\Model\Transport\Transport|null $transport, \App\Model\Payment\Payment|null $payment, \App\Model\Customer\User\CustomerUser|null $customerUser)
 */
class PlaceOrderFacade extends BasePlaceOrderFacade
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitResolver
     */
    private PromoCodeLimitResolver $promoCodeLimitResolver;

    /**
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade $orderProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeLimitResolver $promoCodeLimitResolver
     */
    public function __construct(
        OrderFacade $orderFacade,
        OrderProductFacade $orderProductFacade,
        OrderStatusRepository $orderStatusRepository,
        OrderPreviewFactory $orderPreviewFactory,
        CurrencyFacade $currencyFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        CustomerUserFacade $customerUserFacade,
        PromoCodeFacade $promoCodeFacade,
        PromoCodeLimitResolver $promoCodeLimitResolver
    ) {
        parent::__construct($orderFacade, $orderProductFacade, $orderStatusRepository, $orderPreviewFactory, $currencyFacade, $domain, $currentCustomerUser, $customerUserFacade);

        $this->promoCodeFacade = $promoCodeFacade;
        $this->promoCodeLimitResolver = $promoCodeLimitResolver;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param string|null $promoCodeCode
     * @return \App\Model\Order\Order
     */
    public function placeOrder(OrderData $orderData, array $quantifiedProducts, ?string $promoCodeCode = null): Order
    {
        /** @var \App\Model\Order\Status\OrderStatus $defaultOrderStatus */
        $defaultOrderStatus = $this->orderStatusRepository->getDefault();
        $orderData->status = $defaultOrderStatus;
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $promoCode = $this->findPromoCode($promoCodeCode);

        $orderPreview = $this->orderPreviewFactory->create(
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
            $quantifiedProducts,
            $orderData->transport,
            $orderData->payment,
            $customerUser,
            $this->getPromoCodeDiscountPercent($quantifiedProducts, $promoCode),
            null,
            $promoCode
        );

        $order = $this->orderFacade->createOrder($orderData, $orderPreview, $customerUser);
        $this->orderProductFacade->subtractOrderProductsFromStock($order->getProductItems());

        if ($customerUser instanceof CustomerUser) {
            $this->customerUserFacade->amendCustomerUserDataFromOrder($customerUser, $order, null);
        }

        return $order;
    }

    /**
     * @param string|null $promoCodeCode
     * @return \App\Model\Order\PromoCode\PromoCode|null
     */
    private function findPromoCode(?string $promoCodeCode): ?PromoCode
    {
        if ($promoCodeCode === null) {
            return null;
        }

        return $this->promoCodeFacade->findPromoCodeByCode($promoCodeCode);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return string|null
     */
    private function getPromoCodeDiscountPercent(array $quantifiedProducts, ?PromoCode $promoCode): ?string
    {
        if ($promoCode === null) {
            return null;
        }
        $limit = $this->promoCodeLimitResolver->getLimitByPromoCode(
            $promoCode,
            $quantifiedProducts
        );

        return $limit !== null ? $limit->getDiscount() : null;
    }
}
