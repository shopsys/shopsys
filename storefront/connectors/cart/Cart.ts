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
import { PaymentInputType, PaymentType } from 'types/payment';
import { TransportInputType, TransportType } from 'types/transport';
import { UseMutationResponse, UseQueryResponse } from 'urql';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { mapPriceData } from 'connectors/transports/Transports';
import { mapProductPriceData } from 'connectors/products/Products';
import { PickupPlaceType } from 'types/pickupPlace';
import { useHandleCartErrors } from 'hooks/cart/UseHandleCartErrors';
import { useHandleCartUpdate } from 'hooks/cart/UseHandleCartUpdate';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const mapTransportToTransportInput = (
    transport: TransportType,
    pickupPlace: PickupPlaceType | null,
): TransportInputType => {
    return {
        uuid: transport.uuid,
        price: {
            priceWithVat: transport.price.priceWithVat.toString(),
            priceWithoutVat: transport.price.priceWithoutVat.toString(),
            vatAmount: transport.price.vatAmount.toString(),
        },
        pickupPlaceIdentifier: pickupPlace === null ? null : pickupPlace.identifier,
    };
};

export const mapPaymentToPaymentInput = (payment: PaymentType): PaymentInputType => {
    return {
        uuid: payment.uuid,
        price: {
            priceWithVat: payment.price.priceWithVat.toString(),
            priceWithoutVat: payment.price.priceWithoutVat.toString(),
            vatAmount: payment.price.vatAmount.toString(),
        },
    };
};

export const useLoadCart = (
    cartUuid: CartInput['cartUuid'],
    isCartEmpty: boolean,
    transport: CartInput['transport'],
    payment: CartInput['payment'],
    promoCode: CartInput['promoCode'],
): UseQueryResponse<CartQueryApi, CartQueryVariablesApi> => {
    const [result, refresh] = useCartQueryApi({
        variables: { cartUuid, transport, payment, promoCode },
        pause: isCartEmpty,
        requestPolicy: 'network-only',
    });
    const t = useTypedTranslationFunction();

    useHandleCartErrors(result.error, t('Could not load your cart'));
    useHandleCartUpdate(result.data?.cart);

    return [result, refresh];
};

export const useAddToCart = (): UseMutationResponse<AddToCartMutationApi, AddToCartMutationVariablesApi> => {
    const [addToCartResult, addToCart] = useAddToCartMutationApi();
    const t = useTypedTranslationFunction();

    useHandleCartErrors(addToCartResult.error, t('Could not add the product to cart'));
    useHandleCartUpdate(addToCartResult.data?.AddToCart);

    return [addToCartResult, addToCart];
};

export const useRemoveFromCart = (): UseMutationResponse<RemoveFromCartMutationApi, RemoveFromCartInputApi> => {
    const [removeItemFromCartResult, removeItemFromCart] = useRemoveFromCartMutationApi();
    const t = useTypedTranslationFunction();

    useHandleCartErrors(removeItemFromCartResult.error, t('Could not remove the product from cart'));
    useHandleCartUpdate(removeItemFromCartResult.data?.RemoveFromCart);

    return [removeItemFromCartResult, removeItemFromCart];
};

export const mapCart = (apiData: CartFragmentApi, currencyCode: string): CartType => {
    const remainingFreeTransport = apiData.remainingAmountWithVatForFreeTransport;
    return {
        items: apiData.items.map((item) => {
            return {
                ...item,
                product: {
                    ...item.product,
                    price: mapProductPriceData(item.product.price, currencyCode),
                    availability: item.product.availability.name,
                    image:
                        0 in item.product.images &&
                        item.product.images[0].sizes !== undefined &&
                        item.product.images[0].sizes !== null &&
                        0 in item.product.images[0].sizes
                            ? mapImageSizeApiData(item.product.images[0].sizes[0])
                            : null,
                },
            };
        }),
        totalPrice: mapPriceData(apiData.totalPrice, currencyCode),
        totalDiscountPrice: mapPriceData(apiData.totalDiscountPrice, currencyCode),
        remainingAmountWithVatForFreeTransport:
            remainingFreeTransport !== undefined && remainingFreeTransport !== null
                ? Number.parseFloat(remainingFreeTransport)
                : null,
    };
};
