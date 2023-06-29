import { showErrorMessage, showInfoMessage } from 'components/Helpers/toasts';
import {
    CartItemModificationsFragmentApi,
    CartModificationsFragmentApi,
    CartPaymentModificationsFragmentApi,
    CartPromoCodeModificationsFragmentApi,
    CartTransportModificationsFragmentApi,
    ListedStoreFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    useCartQueryApi,
} from 'graphql/generated';
import { ApplicationErrors } from 'helpers/errors/applicationErrors';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { ChangePaymentHandler } from 'hooks/cart/useChangePaymentInCart';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { Translate } from 'next-translate';
import { useMemo } from 'react';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { CurrentCartType } from 'types/cart';
import { GtmMessageOriginType } from 'types/gtm/enums';
import { CombinedError, OperationContext } from 'urql';

export const useCurrentCart = (fromCache = true): CurrentCartType => {
    const { isUserLoggedIn } = useCurrentUserData();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const t = useTypedTranslationFunction();
    const packeteryPickupPoint = usePersistStore((store) => store.packeteryPickupPoint);

    const [result, refetchCart] = useQueryError(
        useCartQueryApi({
            variables: { cartUuid },
            pause: cartUuid === null && !isUserLoggedIn,
            requestPolicy: fromCache ? 'cache-first' : 'network-only',
        }),
    );

    return useMemo(() => {
        if (result.error !== undefined) {
            // EXTEND CART ERRORS HERE
            handleCartError(result.error, t);
        }

        if (
            result.data === undefined ||
            result.fetching ||
            (cartUuid === null && !isUserLoggedIn) ||
            result.error !== undefined ||
            result.data.cart === null
        ) {
            return getEmptyCart(!result.fetching, result.stale, refetchCart);
        }

        // EXTEND CART UPDATE HERE

        return {
            cart: result.data.cart,
            isCartEmpty: result.data.cart.items.length === 0,
            transport: result.data.cart.transport,
            pickupPlace: getSelectedPickupPlace(
                result.data.cart.transport,
                result.data.cart.selectedPickupPlaceIdentifier,
                packeteryPickupPoint,
            ),
            payment: result.data.cart.payment,
            paymentGoPayBankSwift: result.data.cart.paymentGoPayBankSwift,
            promoCode: result.data.cart.promoCode,
            isLoading: result.stale,
            isInitiallyLoaded: !result.fetching,
            modifications: result.data.cart.modifications,
            refetchCart,
        };
    }, [
        result.error,
        result.data,
        result.fetching,
        result.stale,
        cartUuid,
        isUserLoggedIn,
        packeteryPickupPoint,
        refetchCart,
        t,
    ]);
};

const getEmptyCart = (
    isInitiallyLoaded: boolean,
    isLoading: boolean,
    refetchCart: (opts?: Partial<OperationContext> | undefined) => void,
): CurrentCartType => ({
    cart: null,
    isCartEmpty: true,
    transport: null,
    pickupPlace: null,
    payment: null,
    paymentGoPayBankSwift: null,
    promoCode: null,
    isLoading,
    isInitiallyLoaded,
    modifications: null,
    refetchCart,
});

const handleCartError = (error: CombinedError, t: Translate) => {
    const { userError, applicationError } = getUserFriendlyErrors(error, t);

    switch (applicationError?.type) {
        case ApplicationErrors['cart-not-found']:
            break;
        case ApplicationErrors.default:
            showErrorMessage(applicationError.message, GtmMessageOriginType.cart);
            break;
    }

    if (userError?.validation !== undefined) {
        for (const invalidFieldName in userError.validation) {
            showErrorMessage(userError.validation[invalidFieldName].message, GtmMessageOriginType.cart);
        }
    }
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
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi | null,
    pickupPlaceIdentifier: string | null | undefined,
    packeteryPickupPoint: ListedStoreFragmentApi | null,
): ListedStoreFragmentApi | null => {
    if (transport === null || pickupPlaceIdentifier === null) {
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
