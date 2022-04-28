import {
    AddToCartMutationApi,
    CartFragmentApi,
    RemoveFromCartMutationApi,
    RemoveFromCartMutationVariablesApi,
    useCartQueryApi,
    useRemoveFromCartMutationApi,
} from 'graphql/generated';
import { AddToCartPopupDataType, CartType, CurrentCartType } from 'types/cart';
import { ApplicationErrors, getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { CombinedError, UseMutationResponse } from 'urql';
import { mapPriceData, mapProductPriceData } from 'connectors/price/Prices';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { getFirstImage } from 'connectors/image/Image';
import { getSelectedPickupPlace } from 'connectors/transports/pickupPlace/PickupPlace';
import { mapPayment } from 'connectors/payments/Payment';
import { mapSimpleProductApiData } from 'connectors/products/SimpleProduct';
import { mapTransport } from 'connectors/transports/Transports';
import { PriceType } from 'types/price';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { Translate } from 'next-translate';
import { useEffect } from 'react';
import { userActions } from 'redux/slices/user';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useCurrentCart = (): CurrentCartType => {
    const { isUserLoggedIn, cartUuid } = useShopsysSelector((state) => state.user);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const t = useTypedTranslationFunction();

    const [result] = useCartQueryApi({
        variables: { cartUuid },
        pause: cartUuid === null && !isUserLoggedIn,
        requestPolicy: 'network-only',
    });

    if (result.error !== undefined) {
        // EXTEND CART ERRORS HERE
        handleCartError(result.error, t);

        return getEmptyCart();
    }

    if (!result.data?.cart) {
        // EXTEND EMPTY CART HERE
        return getEmptyCart();
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
    };
};

const getEmptyCart = (): CurrentCartType => ({
    cart: null,
    isCartEmpty: true,
    transport: null,
    pickupPlace: null,
    payment: null,
    paymentGoPayBankSwift: null,
    promoCode: null,
});

const handleCartError = (error: CombinedError, t: Translate) => {
    const { userError, applicationError } = getUserFriendlyErrors(error, t);

    switch (applicationError?.type) {
        case ApplicationErrors.CART_NOT_FOUND:
            break;
        case ApplicationErrors.DEFAULT:
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

export const useRemoveFromCart = (): UseMutationResponse<
    RemoveFromCartMutationApi,
    RemoveFromCartMutationVariablesApi
> => {
    const [removeItemFromCartResult, removeItemFromCart] = useRemoveFromCartMutationApi();
    const dispatch = useShopsysDispatch();

    useEffect(() => {
        if (removeItemFromCartResult.data?.RemoveFromCart.uuid !== undefined) {
            dispatch(userActions.setCartUuid(removeItemFromCartResult.data.RemoveFromCart.uuid));
        }
    }, [removeItemFromCartResult]);

    return [removeItemFromCartResult, removeItemFromCart];
};

export const mapCart = (apiData: CartFragmentApi, currencyCode: string): CartType => {
    const remainingFreeTransport = apiData.remainingAmountWithVatForFreeTransport;

    const totalPrice = mapPriceData(apiData.totalPrice, currencyCode);
    const totalItemsPrice: PriceType = {
        priceWithVat: totalPrice.priceWithVat,
        priceWithoutVat: totalPrice.priceWithoutVat,
        vatAmount: totalPrice.vatAmount,
        currencyCode: currencyCode,
    };

    return {
        items: apiData.items.map((item) => {
            return {
                ...item,
                product: {
                    ...item.product,
                    price: mapProductPriceData(item.product.price, currencyCode),
                    availability: item.product.availability.name,
                    image: getFirstImage(item.product.images),
                },
            };
        }),
        totalPrice: totalPrice,
        totalItemsPrice: totalItemsPrice,
        totalDiscountPrice: mapPriceData(apiData.totalDiscountPrice, currencyCode),
        remainingAmountWithVatForFreeTransport:
            remainingFreeTransport !== null ? Number.parseFloat(remainingFreeTransport) : null,
    };
};
