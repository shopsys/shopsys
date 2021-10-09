import { AddToCartResultType, CartApiType, CartInput, CartType } from './types';
import { PaymentApiType, PaymentInputType, PaymentType } from 'connectors/payments/types';
import { StoreType, TransportApiType, TransportInputType, TransportType } from 'connectors/transports/types';
import { useMutation, UseMutationResponse, useQuery, UseQueryResponse } from 'urql';
import { mapPriceData } from 'connectors/transports/Transports';
import { mapProductPriceData } from 'connectors/products/Products';
import { paymentBody } from 'connectors/payments/Payment';
import { transportBody } from 'connectors/transports/Transport';
import { useHandleCartUpdate } from 'hooks/cart/UseHandleCartUpdate';

const cartItemBody = `
    uuid
    quantity
    product {
        uuid
        slug
        fullName
        catalogNumber
        stockQuantity
        flags {
            name
            rgbColor
        }
        images (sizes: "list") {
            sizes {
                url
                width
                height
        }
        }
        stockQuantity
        availability {
            name
        }
        price {
            priceWithVat
            priceWithoutVat
            vatAmount
            isPriceFrom
        }
        availableStoresCount
        unit {
            name
        }
    }
    `;

const cartBody = `
    uuid
    items {
        ${cartItemBody}
    }
    totalPrice {
        priceWithVat
        priceWithoutVat
        vatAmount
    }
    totalDiscountPrice {
        priceWithVat
        priceWithoutVat
        vatAmount
    }
    modifications{
        itemModifications {
            noLongerListableCartItems { 
                ${cartItemBody} 
            }
            cartItemsWithModifiedPrice { 
                ${cartItemBody} 
            }
            cartItemsWithChangedQuantity { 
                ${cartItemBody} 
            } 
            noLongerAvailableCartItemsDueToQuantity { 
                ${cartItemBody} 
            }
        }
        transportModifications {
            transportPriceChanged
            transportUnavailable
            transportWeightLimitExceeded
        }
        paymentModifications {
            paymentPriceChanged
            paymentUnavailable
        }
    }
` as const;

export const cartQuery = `
    query (
            $cartUuid: Uuid
            $transport: TransportInput
            $payment: PaymentInput
            $promoCode: String
        ){
        cart(cartInput: {
            cartUuid: $cartUuid
            transport: $transport
            payment: $payment
            promoCode: $promoCode
        }) {              
            ${cartBody}
            ${transportBody}
            ${paymentBody}
        }
    }
    ` as const;

export const mapTransportToTransportInput = (
    transport: TransportType,
    personalPickupStore: StoreType | null,
): TransportInputType => {
    return {
        uuid: transport.uuid,
        price: {
            priceWithVat: transport.price.priceWithVat.toString(),
            priceWithoutVat: transport.price.priceWithoutVat.toString(),
            vatAmount: transport.price.vatAmount.toString(),
        },
        personalPickupStoreUuid: personalPickupStore === null ? null : personalPickupStore.uuid,
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
    cartUuid: string | null,
    transport: TransportInputType | null,
    payment: PaymentInputType | null,
    promoCode: string | null,
): UseQueryResponse<{ cart: CartApiType; transport: TransportApiType; payment: PaymentApiType }, CartInput> => {
    const [result, refresh] = useQuery({
        query: cartQuery,
        variables: { cartUuid, transport, payment, promoCode },
        pause: cartUuid === null,
    });

    useHandleCartUpdate(
        result,
        transport?.personalPickupStoreUuid === undefined ? null : transport.personalPickupStoreUuid,
        promoCode,
    );

    return [result, refresh];
};

export const mapCart = (apiData: CartApiType, currencyCode: string): CartType => {
    return {
        ...apiData,
        items: apiData.items.map((item) => {
            return {
                ...item,
                product: {
                    ...item.product,
                    price: mapProductPriceData(item.product.price, currencyCode),
                    availability: item.product.availability.name,
                    image: item.product.images.length === 0 ? null : item.product.images[0].sizes[0],
                },
            };
        }),
        totalPrice: mapPriceData(apiData.totalPrice, currencyCode),
        totalDiscountPrice: mapPriceData(apiData.totalDiscountPrice, currencyCode),
    };
};

const removeItemFromCartMutation = `mutation (
            $cartUuid: Uuid! 
            $cartItemUuid: Uuid!
            $transport: TransportInput
            $payment: PaymentInput
            $promoCode: String) {
        RemoveFromCart ( input: {
            cartUuid: $cartUuid
            cartItemUuid: $cartItemUuid
            transport: $transport
            payment: $payment
            promoCode: $promoCode
        }){
            ${cartBody}
            ${transportBody}
            ${paymentBody}
        }
    }` as const;

export const useRemoveItemFromCart = (): UseMutationResponse<
    { RemoveFromCart: CartApiType & { transport: TransportApiType; payment: PaymentApiType } },
    {
        cartUuid: string;
        cartItemUuid: string;
        transport: TransportInputType | null;
        payment: PaymentInputType | null;
        promoCode: string | null;
    }
> => {
    return useMutation(removeItemFromCartMutation);
};

export const changeCartItemQuantityMutation = `mutation (
            $cartUuid: Uuid 
            $productUuid: Uuid! 
            $quantity: Int! 
            $isAbsoluteQuantity: Boolean 
            $transport: TransportInput
            $payment: PaymentInput
            $promoCode: String) {
        AddToCart(input:{
            cartUuid: $cartUuid
            productUuid: $productUuid
            quantity: $quantity
            isAbsoluteQuantity: $isAbsoluteQuantity
            transport: $transport
            payment: $payment
            promoCode: $promoCode
        }){
            ${cartBody}
            ${transportBody}
            ${paymentBody}
            addProductResult {
                notOnStockQuantity
                overLimitQuantity
                isNew
                isQuantityOverLimit
                addedQuantity
            }
        }
    }` as const;

export const useChangeCartItemQuantity = (): UseMutationResponse<
    { AddToCart: AddToCartResultType },
    {
        cartUuid: string | null;
        productUuid: string;
        quantity: number;
        isAbsoluteQuantity: boolean;
        transport: TransportInputType | null;
        payment: PaymentInputType | null;
        promoCode: string | null;
    }
> => {
    return useMutation(changeCartItemQuantityMutation);
};
