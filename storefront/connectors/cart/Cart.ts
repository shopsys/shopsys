import { showErrorMessage, showInfoMessage } from 'components/Helpers/Toasts';
import { mapAvailabilityData } from 'connectors/availability/Availability';
import { getFirstImage } from 'connectors/image/Image';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { mapPayment } from 'connectors/payments/Payment';
import { mapPriceData, mapProductPriceData } from 'connectors/price/Prices';
import { mapSimpleProductApiData } from 'connectors/products/SimpleProduct';
import { getSelectedPickupPlace } from 'connectors/transports/pickupPlace/PickupPlace';
import { mapTransport } from 'connectors/transports/Transports';
import {
    AddToCartMutationApi,
    CartFragmentApi,
    CartItemFragmentApi,
    CartItemModificationsFragmentApi,
    CartModificationsFragmentApi,
    CartPaymentModificationsFragmentApi,
    CartPromoCodeModificationsFragmentApi,
    CartTransportModificationsFragmentApi,
    useCartQueryApi,
} from 'graphql/generated';
import { ApplicationErrors } from 'helpers/errors/applicationErrors';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { Translate } from 'next-translate';
import { useEffect, useMemo, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType, CartItemType, CartType, CurrentCartType } from 'types/cart';
import { CombinedError, OperationContext } from 'urql';

export const useCurrentCart = (fromCache = true): CurrentCartType => {
    const [isInitiallyLoaded, setInitialLoadedState] = useState(false);
    const { isUserLoggedIn } = useCurrentUserData();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const t = useTypedTranslationFunction();

    const [result, refetchCart] = useCartQueryApi({
        variables: { cartUuid },
        pause: cartUuid === null && !isUserLoggedIn,
        requestPolicy: fromCache ? 'cache-first' : 'network-only',
    });

    useEffect(() => {
        if (cartUuid === null && !isUserLoggedIn) {
            setInitialLoadedState(true);

            return;
        }

        if (!result.fetching) {
            setInitialLoadedState(true);
        }
    }, [cartUuid, isUserLoggedIn, result.fetching]);

    return useMemo(() => {
        if (result.data === undefined) {
            return getEmptyCart(isInitiallyLoaded, refetchCart, false);
        }

        if (isInitiallyLoaded !== true) {
            setInitialLoadedState(true);
        }

        if (cartUuid === null && !isUserLoggedIn) {
            return getEmptyCart(isInitiallyLoaded, refetchCart, true);
        }

        if (result.error !== undefined) {
            // EXTEND CART ERRORS HERE
            handleCartError(result.error, t);

            return getEmptyCart(isInitiallyLoaded, refetchCart, true);
        }

        if (result.data.cart === null) {
            // EXTEND EMPTY CART HERE
            return getEmptyCart(isInitiallyLoaded, refetchCart, true);
        }
        // EXTEND CART UPDATE HERE
        const mappedCart = mapCart(result.data.cart, currencyCode);

        const mappedTransport =
            result.data.cart.transport === null ? null : mapTransport(result.data.cart.transport, currencyCode);
        return {
            cart: mappedCart,
            isCartEmpty: mappedCart.items.length === 0,
            transport: mappedTransport,
            pickupPlace: getSelectedPickupPlace(mappedTransport, result.data.cart.selectedPickupPlaceIdentifier),
            payment: result.data.cart.payment === null ? null : mapPayment(result.data.cart.payment, currencyCode),
            paymentGoPayBankSwift: result.data.cart.paymentGoPayBankSwift,
            promoCode: result.data.cart.promoCode,
            isLoaded: true,
            isInitiallyLoaded: isInitiallyLoaded,
            modifications: result.data.cart.modifications,
            refetchCart,
        };
    }, [currencyCode, result.data, result.error, t, cartUuid, isUserLoggedIn, isInitiallyLoaded, refetchCart]);
};

const getEmptyCart = (
    isInitiallyLoaded: boolean,
    refetchCart: (opts?: Partial<OperationContext> | undefined) => void,
    isLoaded = true,
): CurrentCartType => ({
    cart: null,
    isCartEmpty: true,
    transport: null,
    pickupPlace: null,
    payment: null,
    paymentGoPayBankSwift: null,
    promoCode: null,
    isLoaded,
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
            showErrorMessage(applicationError.message, 'cart');
            break;
    }

    if (userError?.validation !== undefined) {
        for (const invalidFieldName in userError.validation) {
            showErrorMessage(userError.validation[invalidFieldName].message, 'cart');
        }
    }
};

export const mapAddToCartPopupData = (
    addToCartResult: AddToCartMutationApi['AddToCart'] | null,
    currencyCode: string,
): AddToCartPopupDataType | null => {
    if (addToCartResult === null) {
        return null;
    }

    return {
        ...mapSimpleProductApiData(addToCartResult.addProductResult.cartItem.product, currencyCode),
        quantity: addToCartResult.addProductResult.addedQuantity,
    };
};

export const mapCart = (apiData: CartFragmentApi, currencyCode: string): CartType => {
    const remainingFreeTransport = apiData.remainingAmountWithVatForFreeTransport;

    const totalPrice = mapPriceData(apiData.totalPrice, currencyCode);
    const totalItemsPrice = mapPriceData(apiData.totalItemsPrice, currencyCode);

    return {
        items: apiData.items.map((item) => mapCartItem(item, currencyCode)),
        totalPrice: totalPrice,
        totalItemsPrice: totalItemsPrice,
        totalDiscountPrice: mapPriceData(apiData.totalDiscountPrice, currencyCode),
        remainingAmountWithVatForFreeTransport:
            remainingFreeTransport !== null ? Number.parseFloat(remainingFreeTransport) : null,
    };
};

export const mapCartItem = (apiData: CartItemFragmentApi, currencyCode: string): CartItemType => {
    return {
        ...apiData,
        product: {
            ...apiData.product,
            price: mapProductPriceData(apiData.product.price, currencyCode),
            availability: mapAvailabilityData(apiData.product.availability),
            image: getFirstImage(apiData.product.images),
            categoryNames: apiData.product.categories.map((category) => category.name),
        },
    };
};

export const handleCartModifications = (
    cartModifications: CartModificationsFragmentApi,
    t: Translate,
    changePaymentInCart: ReturnType<typeof useChangePaymentInCart>,
): void => {
    handleCartTransportModifications(cartModifications.transportModifications, t, changePaymentInCart);
    handleCartPaymentModifications(cartModifications.paymentModifications, t);
    handleCartItemModifications(cartModifications.itemModifications, t);
    handleCartPromoCodeModifications(cartModifications.promoCodeModifications, t);
};

const handleCartTransportModifications = (
    transportModifications: CartTransportModificationsFragmentApi,
    t: Translate,
    changePaymentInCart: ReturnType<typeof useChangePaymentInCart>,
): void => {
    if (transportModifications.transportPriceChanged) {
        showInfoMessage(t('The price of the transport you selected has changed.'), 'cart');
    }
    if (transportModifications.transportUnavailable) {
        changePaymentInCart(null, null);
        showInfoMessage(t('The transport you selected is no longer available.'), 'cart');
        showInfoMessage(t('Your transport and payment selection has been removed.'), 'cart');
    }
    if (transportModifications.transportWeightLimitExceeded) {
        changePaymentInCart(null, null);
        showInfoMessage(t('You have exceeded the weight limit of the selected transport.'), 'cart');
        showInfoMessage(t('Your transport and payment selection has been removed.'), 'cart');
    }
};

const handleCartPaymentModifications = (
    paymentModifications: CartPaymentModificationsFragmentApi,
    t: Translate,
): void => {
    if (paymentModifications.paymentPriceChanged) {
        showInfoMessage(t('The price of the payment you selected has changed.'), 'cart');
    }
    if (paymentModifications.paymentUnavailable) {
        showInfoMessage(t('The payment you selected is no longer available.'), 'cart');
    }
};

const handleCartItemModifications = (itemModifications: CartItemModificationsFragmentApi, t: Translate): void => {
    for (const cartItemWithChangedQuantity of itemModifications.cartItemsWithChangedQuantity) {
        showInfoMessage(
            t('The quantity of item {{ itemName }} has changed.', {
                itemName: cartItemWithChangedQuantity.product.fullName,
            }),
            'cart',
        );
    }
    for (const cartItemWithModifiedPrice of itemModifications.cartItemsWithModifiedPrice) {
        showInfoMessage(
            t('The price of item {{ itemName }} has changed.', {
                itemName: cartItemWithModifiedPrice.product.fullName,
            }),
            'cart',
        );
    }
    for (const soldOutCartItem of itemModifications.noLongerAvailableCartItemsDueToQuantity) {
        showInfoMessage(
            t('Item {{ itemName }} has been sold out.', { itemName: soldOutCartItem.product.fullName }),
            'cart',
        );
    }
    for (const nonListableCartItem of itemModifications.noLongerListableCartItems) {
        showInfoMessage(
            t('Item {{ itemName }} can no longer be bought.', { itemName: nonListableCartItem.product.fullName }),
            'cart',
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
            'cart',
        );
    }
};
