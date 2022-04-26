import {
    AddToCartMutationApi,
    AddToCartMutationVariablesApi,
    CartFragmentApi,
    CartQueryApi,
    CartQueryVariablesApi,
    RemoveFromCartInputApi,
    RemoveFromCartMutationApi,
    useAddToCartMutationApi,
    useCartQueryApi,
    useRemoveFromCartMutationApi,
} from 'graphql/generated';
import { CartInput, CartType } from 'types/cart';
import { mapPriceData, mapPriceInputData, mapProductPriceData } from 'connectors/price/Prices';
import { PaymentInputType, PaymentType } from 'types/payment';
import { TransportInputType, TransportType } from 'types/transport';
import { UseMutationResponse, UseQueryResponse } from 'urql';
import { getFirstImage } from 'connectors/image/Image';
import { PickupPlaceType } from 'types/pickupPlace';
import { PriceType } from 'types/price';
import { useHandleCartErrors } from 'hooks/cart/UseHandleCartErrors';
import { useHandleCartUpdate } from 'hooks/cart/UseHandleCartUpdate';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const mapTransportToTransportInput = (
    transport: TransportType,
    pickupPlace: PickupPlaceType | null,
): TransportInputType => {
    return {
        uuid: transport.uuid,
        price: mapPriceInputData(transport.price),
        pickupPlaceIdentifier: pickupPlace === null ? null : pickupPlace.identifier,
    };
};

export const mapPaymentToPaymentInput = (payment: PaymentType, goPayBankSwift: string | null): PaymentInputType => {
    return {
        uuid: payment.uuid,
        price: {
            priceWithVat: payment.price.priceWithVat.toString(),
            priceWithoutVat: payment.price.priceWithoutVat.toString(),
            vatAmount: payment.price.vatAmount.toString(),
        },
        goPayBankSwift,
    };
};

export const useLoadCart = (
    cartUuid: CartInput['cartUuid'],
    transport: CartInput['transport'],
    payment: CartInput['payment'],
    goPayBankSwift: string | null,
): UseQueryResponse<CartQueryApi> => {
    const { isUserLoggedIn } = useShopsysSelector((state) => state.user);
    const [result, refresh] = useCartQueryApi({
        variables: { cartUuid, transport, payment } as CartQueryVariablesApi,
        pause: cartUuid === null && !isUserLoggedIn,
        requestPolicy: 'network-only',
    });
    const t = useTypedTranslationFunction();

    useHandleCartErrors(result.error, t('Could not load your cart'));
    useHandleCartUpdate(result.data?.cart, goPayBankSwift);

    return [result, refresh];
};

export const useAddToCart = (): UseMutationResponse<AddToCartMutationApi, AddToCartMutationVariablesApi> => {
    const { cartInput } = useShopsysSelector((state) => state.cart);
    const [addToCartResult, addToCart] = useAddToCartMutationApi();
    const t = useTypedTranslationFunction();

    useEffect(() => {
        if (addToCartResult.data?.AddToCart.cart.uuid !== undefined) {
            dispatch(userActions.setCartUuid(addToCartResult.data.AddToCart.cart.uuid));
        }
    }, [addToCartResult.data?.AddToCart.cart.uuid]);

    return [addToCartResult, addToCart];
};

export const useRemoveFromCart = (): UseMutationResponse<RemoveFromCartMutationApi, RemoveFromCartInputApi> => {
    const { cartInput } = useShopsysSelector((state) => state.cart);
    const [removeItemFromCartResult, removeItemFromCart] = useRemoveFromCartMutationApi();
    const t = useTypedTranslationFunction();

    useHandleCartErrors(removeItemFromCartResult.error, t('Could not remove the product from cart'));
    useHandleCartUpdate(
        removeItemFromCartResult.data?.RemoveFromCart,
        cartInput.payment ? cartInput.payment.goPayBankSwift : null,
    );

    return [removeItemFromCartResult, removeItemFromCart];
};

export const mapCart = (
    apiData: CartFragmentApi,
    transportPrice: PriceType,
    paymentPrice: PriceType,
    currencyCode: string,
): CartType => {
    const remainingFreeTransport = apiData.remainingAmountWithVatForFreeTransport;

    const totalPrice = mapPriceData(apiData.totalPrice, currencyCode);
    const totalItemsPrice: PriceType = {
        priceWithVat: totalPrice.priceWithVat - transportPrice.priceWithVat - paymentPrice.priceWithVat,
        priceWithoutVat: totalPrice.priceWithoutVat - transportPrice.priceWithoutVat - paymentPrice.priceWithoutVat,
        vatAmount: totalPrice.vatAmount - transportPrice.vatAmount - paymentPrice.vatAmount,
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
