import {
    CartItemModificationsFragmentApi,
    CartModificationsFragmentApi,
    CartPaymentModificationsFragmentApi,
    CartPromoCodeModificationsFragmentApi,
    CartTransportModificationsFragmentApi,
    ListedStoreFragmentApi,
    Maybe,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    useCartQueryApi,
} from 'graphql/generated';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { showInfoMessage } from 'helpers/toasts';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { ChangePaymentHandler } from 'hooks/cart/useChangePaymentInCart';
import { Translate } from 'next-translate';
import { usePersistStore } from 'store/usePersistStore';
import { CurrentCartType } from 'types/cart';

export const useCurrentCart = (fromCache = true): CurrentCartType => {
    const isUserLoggedIn = useIsUserLoggedIn();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);

    const [{ data: cartData, stale, fetching }, refetchCart] = useCartQueryApi({
        variables: { cartUuid },
        pause: !cartUuid && !isUserLoggedIn,
        requestPolicy: fromCache ? 'cache-first' : 'network-only',
    });

    return {
        refetchCart,
        cart: cartData?.cart ?? null,
        isCartEmpty: !cartData?.cart?.items.length,
        transport: cartData?.cart?.transport ?? null,
        pickupPlace: getSelectedPickupPlace(
            cartData?.cart?.transport,
            cartData?.cart?.selectedPickupPlaceIdentifier,
            packeteryPickupPoint,
        ),
        payment: cartData?.cart?.payment ?? null,
        paymentGoPayBankSwift: cartData?.cart?.paymentGoPayBankSwift ?? null,
        promoCode: cartData?.cart?.promoCode ?? null,
        isLoading: stale,
        isFetching: fetching,
        modifications: cartData?.cart?.modifications ?? null,
        roundingPrice: cartData?.cart?.roundingPrice ?? null,
    };
};

export const handleCartModifications = (
    cartModifications: CartModificationsFragmentApi,
    t: Translate,
    changePaymentInCart: ChangePaymentHandler,
): void => {
    handleRemovedProductFromEshopModifications(cartModifications.someProductWasRemovedFromEshop, t);
    handleCartTransportModifications(cartModifications.transportModifications, t, changePaymentInCart);
    handleCartPaymentModifications(cartModifications.paymentModifications, t);
    handleCartItemModifications(cartModifications.itemModifications, t);
    handleCartPromoCodeModifications(cartModifications.promoCodeModifications, t);
};

const handleRemovedProductFromEshopModifications = (someProductWasRemovedFromEshop: boolean, t: Translate): void => {
    if (someProductWasRemovedFromEshop) {
        showInfoMessage(
            t('Some product was removed from e-shop and your cart was recalculated.'),
            GtmMessageOriginType.cart,
        );
    }
};

const handleCartTransportModifications = (
    transportModifications: CartTransportModificationsFragmentApi,
    t: Translate,
    changePaymentInCart: ChangePaymentHandler,
): void => {
    if (transportModifications.transportPriceChanged) {
        showInfoMessage(t('The price of the transport you selected has changed.'), GtmMessageOriginType.cart);
    }
    if (transportModifications.transportUnavailable) {
        changePaymentInCart(null, null);
        showInfoMessage(t('The transport you selected is no longer available.'), GtmMessageOriginType.cart);
        showInfoMessage(t('Your transport and payment selection has been removed.'), GtmMessageOriginType.cart);
    }
    if (transportModifications.transportWeightLimitExceeded) {
        changePaymentInCart(null, null);
        showInfoMessage(t('You have exceeded the weight limit of the selected transport.'), GtmMessageOriginType.cart);
        showInfoMessage(t('Your transport and payment selection has been removed.'), GtmMessageOriginType.cart);
    }
};

const handleCartPaymentModifications = (
    paymentModifications: CartPaymentModificationsFragmentApi,
    t: Translate,
): void => {
    if (paymentModifications.paymentPriceChanged) {
        showInfoMessage(t('The price of the payment you selected has changed.'), GtmMessageOriginType.cart);
    }
    if (paymentModifications.paymentUnavailable) {
        showInfoMessage(t('The payment you selected is no longer available.'), GtmMessageOriginType.cart);
    }
};

const handleCartItemModifications = (itemModifications: CartItemModificationsFragmentApi, t: Translate): void => {
    for (const cartItemWithChangedQuantity of itemModifications.cartItemsWithChangedQuantity) {
        showInfoMessage(
            t('The quantity of item {{ itemName }} has changed.', {
                itemName: cartItemWithChangedQuantity.product.fullName,
            }),
            GtmMessageOriginType.cart,
        );
    }
    for (const cartItemWithModifiedPrice of itemModifications.cartItemsWithModifiedPrice) {
        showInfoMessage(
            t('The price of item {{ itemName }} has changed.', {
                itemName: cartItemWithModifiedPrice.product.fullName,
            }),
            GtmMessageOriginType.cart,
        );
    }
    for (const soldOutCartItem of itemModifications.noLongerAvailableCartItemsDueToQuantity) {
        showInfoMessage(
            t('Item {{ itemName }} has been sold out.', { itemName: soldOutCartItem.product.fullName }),
            GtmMessageOriginType.cart,
        );
    }
    for (const nonListableCartItem of itemModifications.noLongerListableCartItems) {
        showInfoMessage(
            t('Item {{ itemName }} can no longer be bought.', { itemName: nonListableCartItem.product.fullName }),
            GtmMessageOriginType.cart,
        );
    }
};

const handleCartPromoCodeModifications = (
    promoCodeModifications: CartPromoCodeModificationsFragmentApi,
    t: Translate,
): void => {
    for (const nonApplicablePromoCode of promoCodeModifications.noLongerApplicablePromoCode) {
        showInfoMessage(
            t('The promo code {{ promoCode }} is no longer applicable.', { promoCode: nonApplicablePromoCode }),
            GtmMessageOriginType.cart,
        );
    }
};

const getSelectedPickupPlace = (
    transport: Maybe<TransportWithAvailablePaymentsAndStoresFragmentApi> | undefined,
    pickupPlaceIdentifier: string | null | undefined,
    packeteryPickupPoint: ListedStoreFragmentApi | null,
): ListedStoreFragmentApi | null => {
    if (!transport || !pickupPlaceIdentifier) {
        return null;
    }

    if (transport.transportType.code === 'packetery') {
        return packeteryPickupPoint;
    }

    const pickupPlace = transport.stores?.edges?.find(
        (pickupPlaceNode) => pickupPlaceNode?.node?.identifier === pickupPlaceIdentifier,
    );

    return pickupPlace?.node === undefined ? null : pickupPlace.node;
};
