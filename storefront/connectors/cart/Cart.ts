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
    CartPaymentModificationsFragmentApi,
    CartPromoCodeModificationsFragmentApi,
    CartTransportModificationsFragmentApi,
    useCartQueryApi,
} from 'graphql/generated';
import { ApplicationErrors } from 'helpers/errors/applicationErrors';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { Translate } from 'next-translate';
import { useMemo, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType, CartItemType, CartType, CurrentCartType } from 'types/cart';
import { CombinedError } from 'urql';

export const useCurrentCart = (fromCache = true): CurrentCartType => {
    const isInitiallyLoaded = useRef(false);
    const { isUserLoggedIn } = useCurrentUserData();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const t = useTypedTranslationFunction();

    const [result] = useCartQueryApi({
        variables: { cartUuid },
        pause: cartUuid === null && !isUserLoggedIn,
        requestPolicy: fromCache ? 'cache-first' : 'network-only',
    });

    return useMemo(() => {
        if (result.data === undefined) {
            return getEmptyCart(isInitiallyLoaded.current, false);
        }

        if (isInitiallyLoaded.current !== true) {
            isInitiallyLoaded.current = true;
        }

        if (cartUuid === null && !isUserLoggedIn) {
            return getEmptyCart(isInitiallyLoaded.current, true);
        }

        if (result.error !== undefined) {
            // EXTEND CART ERRORS HERE
            handleCartError(result.error, t);

            return getEmptyCart(isInitiallyLoaded.current, true);
        }

        if (result.data.cart === null) {
            // EXTEND EMPTY CART HERE
            return getEmptyCart(isInitiallyLoaded.current, true);
        }

        handleCartModifications(result.data.cart, t);

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
            isInitiallyLoaded: isInitiallyLoaded.current,
        };
    }, [currencyCode, result.data, result.error, t, cartUuid, isUserLoggedIn, isInitiallyLoaded]);
};

const getEmptyCart = (isInitiallyLoaded: boolean, isLoaded = true): CurrentCartType => ({
    cart: null,
    isCartEmpty: true,
    transport: null,
    pickupPlace: null,
    payment: null,
    paymentGoPayBankSwift: null,
    promoCode: null,
    isLoaded,
    isInitiallyLoaded,
});

const handleCartError = (error: CombinedError, t: Translate) => {
    const { userError, applicationError } = getUserFriendlyErrors(error, t);

    switch (applicationError?.type) {
        case ApplicationErrors['cart-not-found']:
            break;
        case ApplicationErrors.default:
            showErrorMessage(applicationError.message);
            break;
    }

    if (userError?.validation !== undefined) {
        for (const invalidFieldName in userError.validation) {
            showErrorMessage(userError.validation[invalidFieldName].message);
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

const handleCartModifications = (cart: CartFragmentApi, t: Translate): void => {
    handleCartTransportModifications(cart.modifications.transportModifications, t);
    handleCartPaymentModifications(cart.modifications.paymentModifications, t);
    handleCartItemModifications(cart.modifications.itemModifications, t);
    handleCartPromoCodeModifications(cart.modifications.promoCodeModifications, t);
};

const handleCartTransportModifications = (
    transportModifications: CartTransportModificationsFragmentApi,
    t: Translate,
): void => {
    if (transportModifications.transportPriceChanged) {
        showInfoMessage(t('The price of the transport you selected has changed.'));
    }
    if (transportModifications.transportUnavailable) {
        showInfoMessage(t('The transport you selected is no longer available.'));
    }
    if (transportModifications.transportWeightLimitExceeded) {
        showInfoMessage(t('You have exceeded the weight limit of the selected transport.'));
    }
};

const handleCartPaymentModifications = (
    paymentModifications: CartPaymentModificationsFragmentApi,
    t: Translate,
): void => {
    if (paymentModifications.paymentPriceChanged) {
        showInfoMessage(t('The price of the payment you selected has changed.'));
    }
    if (paymentModifications.paymentUnavailable) {
        showInfoMessage(t('The payment you selected is no longer available.'));
    }
};

const handleCartItemModifications = (itemModifications: CartItemModificationsFragmentApi, t: Translate): void => {
    for (const cartItemWithChangedQuantity of itemModifications.cartItemsWithChangedQuantity) {
        showInfoMessage(
            t('The quantity of item {{ itemName }} has changed.', {
                itemName: cartItemWithChangedQuantity.product.fullName,
            }),
        );
    }
    for (const cartItemWithModifiedPrice of itemModifications.cartItemsWithModifiedPrice) {
        showInfoMessage(
            t('The price of item {{ itemName }} has changed.', {
                itemName: cartItemWithModifiedPrice.product.fullName,
            }),
        );
    }
    for (const soldOutCartItem of itemModifications.noLongerAvailableCartItemsDueToQuantity) {
        showInfoMessage(t('Item {{ itemName }} has been sold out.', { itemName: soldOutCartItem.product.fullName }));
    }
    for (const nonListableCartItem of itemModifications.noLongerListableCartItems) {
        showInfoMessage(
            t('Item {{ itemName }} can no longer be bought.', { itemName: nonListableCartItem.product.fullName }),
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
        );
    }
};
