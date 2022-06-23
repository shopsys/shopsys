import { showErrorMessage } from 'components/Helpers/Toasts';
import { mapAvailabilityData } from 'connectors/availability/Availability';
import { getFirstImage } from 'connectors/image/Image';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { mapPayment } from 'connectors/payments/Payment';
import { mapPriceData, mapProductPriceData } from 'connectors/price/Prices';
import { mapSimpleProductApiData } from 'connectors/products/SimpleProduct';
import { getSelectedPickupPlace } from 'connectors/transports/pickupPlace/PickupPlace';
import { mapTransport } from 'connectors/transports/Transports';
import { AddToCartMutationApi, CartFragmentApi, CartItemFragmentApi, useCartQueryApi } from 'graphql/generated';
import { ApplicationErrors } from 'helpers/errors/applicationErrors';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { Translate } from 'next-translate';
import { useMemo, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType, CartItemType, CartType, CurrentCartType } from 'types/cart';
import { CombinedError } from 'urql';

export const useCurrentCart = (): CurrentCartType => {
    const isInitiallyLoaded = useRef(false);
    const { isUserLoggedIn } = useCurrentUserData();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const t = useTypedTranslationFunction();

    const [result] = useCartQueryApi({
        variables: { cartUuid },
        pause: cartUuid === null && !isUserLoggedIn,
        requestPolicy: 'network-only',
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
