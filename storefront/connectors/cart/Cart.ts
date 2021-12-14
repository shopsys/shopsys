import { CartFragmentApi, CartQueryApi, CartQueryVariablesApi, useCartQueryApi } from 'graphql/generated';
import { CartInput, CartType } from 'types/cart';
import { PaymentInputType, PaymentType } from 'types/payment';
import { TransportInputType, TransportType } from 'types/transport';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { mapPriceData } from 'connectors/transports/Transports';
import { mapProductPriceData } from 'connectors/products/Products';
import { PickupPlaceType } from 'types/pickupPlace';
import { useHandleCartUpdate } from 'hooks/cart/UseHandleCartUpdate';
import { UseQueryResponse } from 'urql';

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

export const loadCart = (
    cartUuid: CartInput['cartUuid'],
    isCartEmpty: CartInput['isCartEmpty'],
    transport: CartInput['transport'],
    payment: CartInput['payment'],
    promoCode: CartInput['promoCode'],
): UseQueryResponse<CartQueryApi, CartQueryVariablesApi> => {
    const [result, refresh] = useCartQueryApi({
        variables: { cartUuid, transport, payment, promoCode },
        pause: isCartEmpty,
        requestPolicy: 'network-only',
    });

    useHandleCartUpdate(
        result,
        transport?.pickupPlaceIdentifier === undefined ? null : transport.pickupPlaceIdentifier,
        promoCode,
    );

    return [result, refresh];
};

export const mapCart = (apiData: CartFragmentApi, currencyCode: string): CartType => {
    const remainingFreeTransport = apiData.remainingAmountWithVatForFreeTransport;
    return {
        ...apiData,
        uuid: apiData.uuid !== undefined ? apiData.uuid : null,
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
