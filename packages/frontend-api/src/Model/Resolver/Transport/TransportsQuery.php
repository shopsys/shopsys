<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use ArrayObject;
use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\Exception\TransportIsNotPersonalPickupException;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportUnavailabilityReasonInCartEnum;
use Shopsys\FrameworkBundle\Model\Transport\TransportVisibilityCalculation;
use Shopsys\FrontendApiBundle\Component\GqlContext\GqlContextHelper;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Transport\Exception\TransportNotFoundUserError;

class TransportsQuery extends AbstractQuery
{
    protected const string CART_CACHE_NAMESPACE = 'transportsQueryCart';
    protected const string EXCLUDING_PRODUCTS_CACHE_NAMESPACE = 'transportsQueryExcludingProductsByTransportId';
    protected const string TRANSPORT_CACHE_NAMESPACE = 'transportsQueryTransportByUuid';
    protected const string CURRENT_CUSTOMER_CART_CACHE_KEY = 'currentCustomerCart';

    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly GqlContextHelper $gqlContextHelper,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly TransportVisibilityCalculation $transportVisibilityCalculation,
        protected readonly TransportExpectedDeliveryDateCalculation $transportExpectedDeliveryDateCalculation,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function transportsQuery(?string $cartUuid = null): array
    {
        $cart = $this->findCart($cartUuid);

        if ($cart === null) {
            return $this->filterOutEmailTransports(
                $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations(),
            );
        }

        $transports = $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations($cart);

        if (!$cart->hasOnlyElectronicGiftVoucherProducts()) {
            $transports = $this->filterOutEmailTransports($transports);
        }

        return $this->sortAvailableTransportsFirst($transports, $cart, $cartUuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    protected function filterOutEmailTransports(array $transports): array
    {
        return array_values(array_filter(
            $transports,
            static fn (Transport $transport): bool => !$transport->isEmailType(),
        ));
    }

    /**
     * @return array<int, array{reason: string, products: \Shopsys\FrameworkBundle\Model\Product\Product[]}>
     */
    public function transportProductsBlockingSelectionInCartQuery(
        Transport $transport,
        ?string $cartUuid = null,
        ?ArrayObject $context = null,
    ): array {
        $resolvedCartUuid = $cartUuid ?? $this->gqlContextHelper->getCartUuid($context);
        $cart = $this->findCart($resolvedCartUuid);

        if ($cart === null) {
            return [];
        }

        return $this->getProductsBlockingSelectionGroupedByReason($transport, $cart, $resolvedCartUuid);
    }

    /**
     * @return array<int, array{reason: string, products: \Shopsys\FrameworkBundle\Model\Product\Product[]}>
     */
    protected function getProductsBlockingSelectionGroupedByReason(
        Transport $transport,
        Cart $cart,
        ?string $cartUuid,
    ): array {
        $productsGroupedByReason = [];

        $excludingProducts = $this->getExcludingProductsByTransportId($cart, $cartUuid)[$transport->getId()] ?? [];

        if ($excludingProducts !== []) {
            $productsGroupedByReason[] = [
                'reason' => TransportUnavailabilityReasonInCartEnum::EXCLUDED_FOR_PRODUCT,
                'products' => $excludingProducts,
            ];
        }

        $personalPickupOnlyProducts = array_values($cart->getPersonalPickupOnlyProducts());

        if (!$transport->isPersonalPickup() && $personalPickupOnlyProducts !== []) {
            $productsGroupedByReason[] = [
                'reason' => TransportUnavailabilityReasonInCartEnum::PERSONAL_PICKUP_REQUIRED,
                'products' => $personalPickupOnlyProducts,
            ];
        }

        $productsOtherThanElectronicGiftVouchers = array_values($cart->getProductsOtherThanElectronicGiftVouchers());

        if ($transport->isEmailType() && $productsOtherThanElectronicGiftVouchers !== []) {
            $productsGroupedByReason[] = [
                'reason' => TransportUnavailabilityReasonInCartEnum::EMAIL_TRANSPORT_NOT_ALLOWED,
                'products' => $productsOtherThanElectronicGiftVouchers,
            ];
        }

        $electronicGiftVoucherProducts = array_values($cart->getElectronicGiftVoucherProducts());

        if (!$transport->isEmailType() && $productsOtherThanElectronicGiftVouchers === [] && $electronicGiftVoucherProducts !== []) {
            $productsGroupedByReason[] = [
                'reason' => TransportUnavailabilityReasonInCartEnum::ELECTRONIC_GIFT_VOUCHER_ONLY,
                'products' => $electronicGiftVoucherProducts,
            ];
        }

        return $productsGroupedByReason;
    }

    public function transportExpectedDeliveryDateQuery(
        Transport $transport,
        ?string $cartUuid = null,
        ?ArrayObject $context = null,
    ): ?DateTimeImmutable {
        $resolvedCartUuid = $cartUuid ?? $this->gqlContextHelper->getCartUuid($context);

        return $this->transportExpectedDeliveryDateCalculation->calculateExpectedDeliveryDate(
            $transport,
            $this->findCart($resolvedCartUuid),
            $this->domain->getId(),
        );
    }

    public function storeExpectedDeliveryDateQuery(
        Store $store,
        string $transportUuid,
        ?string $cartUuid = null,
        ?ArrayObject $context = null,
    ): ?DateTimeImmutable {
        $transport = $this->getEnabledTransportByUuidCached($transportUuid);
        $resolvedCartUuid = $cartUuid ?? $this->gqlContextHelper->getCartUuid($context);

        try {
            return $this->transportExpectedDeliveryDateCalculation->calculateExpectedDeliveryDateForStore(
                $transport,
                $this->findCart($resolvedCartUuid),
                $this->domain->getId(),
                $store,
            );
        } catch (TransportIsNotPersonalPickupException $transportIsNotPersonalPickupException) {
            throw new InvalidArgumentUserError($transportIsNotPersonalPickupException->getMessage());
        }
    }

    /**
     * The store picker resolves the field once per store, always with the same transport uuid
     */
    protected function getEnabledTransportByUuidCached(string $transportUuid): Transport
    {
        return $this->inMemoryCache->getOrSaveValue(
            self::TRANSPORT_CACHE_NAMESPACE,
            function () use ($transportUuid): Transport {
                try {
                    return $this->transportFacade->getEnabledOnDomainByUuid($transportUuid, $this->domain->getId());
                } catch (TransportNotFoundException $transportNotFoundException) {
                    throw new TransportNotFoundUserError($transportNotFoundException->getMessage());
                }
            },
            $transportUuid,
            $this->domain->getId(),
        );
    }

    protected function findCart(?string $cartUuid): ?Cart
    {
        return $this->inMemoryCache->getOrSaveValue(
            self::CART_CACHE_NAMESPACE,
            function () use ($cartUuid): ?Cart {
                $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

                if ($customerUser === null && $cartUuid === null) {
                    return null;
                }

                return $this->cartApiFacade->findCart($customerUser, $cartUuid);
            },
            $cartUuid ?? self::CURRENT_CUSTOMER_CART_CACHE_KEY,
        );
    }

    /**
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Product[]>
     */
    protected function getExcludingProductsByTransportId(Cart $cart, ?string $cartUuid): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            self::EXCLUDING_PRODUCTS_CACHE_NAMESPACE,
            fn (): array => $this->transportVisibilityCalculation->getExcludingProductsByTransportIdForCart($cart),
            $cartUuid ?? self::CURRENT_CUSTOMER_CART_CACHE_KEY,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    protected function sortAvailableTransportsFirst(array $transports, Cart $cart, ?string $cartUuid): array
    {
        $availableTransports = [];
        $unavailableTransports = [];

        foreach ($transports as $transport) {
            if ($this->getProductsBlockingSelectionGroupedByReason($transport, $cart, $cartUuid) === []) {
                $availableTransports[] = $transport;
            } else {
                $unavailableTransports[] = $transport;
            }
        }

        return [...$availableTransports, ...$unavailableTransports];
    }
}
