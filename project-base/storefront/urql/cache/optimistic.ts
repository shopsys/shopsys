import { OptimisticMutationConfig } from '@urql/exchange-graphcache';
import {
    ChangePaymentInCartMutationVariables,
    ChangePaymentInCartMutation,
} from 'graphql/requests/cart/mutations/ChangePaymentInCartMutation.generated';
import {
    ChangeTransportInCartMutationVariables,
    ChangeTransportInCartMutation,
} from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.generated';
import { CartQuery, CartQueryVariables, CartQueryDocument } from 'graphql/requests/cart/queries/CartQuery.generated';
import { TransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import {
    TransportsQuery,
    TransportsQueryVariables,
    TransportsQueryDocument,
} from 'graphql/requests/transports/queries/TransportsQuery.generated';
import { ChangeTransportInCartInput, ChangePaymentInCartInput } from 'graphql/types';

export const optimisticUpdates: OptimisticMutationConfig = {
    ChangeTransportInCart: ({ input }: ChangeTransportInCartMutationVariables, cache) => {
        const cartQueryResult: CartQuery | null = cache.readQuery<CartQuery, CartQueryVariables>({
            query: CartQueryDocument,
            variables: {
                cartUuid: input.cartUuid ?? null,
            },
        });

        const transportsQueryResult = cache.readQuery<TransportsQuery, TransportsQueryVariables>({
            query: TransportsQueryDocument,
            variables: {
                cartUuid: input.cartUuid ?? null,
            },
        });

        if (cartQueryResult === null) {
            return null;
        }

        return getOptimisticChangeTransportInCartResult(cartQueryResult, transportsQueryResult, input);
    },
    ChangePaymentInCart: ({ input }: ChangePaymentInCartMutationVariables, cache) => {
        const cartQueryResult: CartQuery | null = cache.readQuery<CartQuery, CartQueryVariables>({
            query: CartQueryDocument,
            variables: {
                cartUuid: input.cartUuid ?? null,
            },
        });

        if (cartQueryResult === null) {
            return null;
        }

        return getOptimisticChangePaymentInCartResult(cartQueryResult, input);
    },
    RemoveProductList: () => {
        return {
            __typename: 'ProductList',
            productList: null,
        };
    },
};

const getOptimisticChangeTransportInCartResult = (
    cartQueryResult: CartQuery,
    transportsQueryResult: TransportsQuery | null,
    input: ChangeTransportInCartInput,
) =>
    ({
        __typename: 'Cart',
        items: cartQueryResult.cart?.items ?? null,
        modifications: cartQueryResult.cart?.modifications ?? null,
        payment: cartQueryResult.cart?.payment ?? null,
        paymentGoPayBankSwift: cartQueryResult.cart?.paymentGoPayBankSwift ?? null,
        promoCode: cartQueryResult.cart?.promoCode ?? null,
        remainingAmountWithVatForFreeTransport: cartQueryResult.cart?.remainingAmountWithVatForFreeTransport ?? null,
        selectedPickupPlaceIdentifier: input.pickupPlaceIdentifier ?? null,
        totalDiscountPrice: cartQueryResult.cart?.totalDiscountPrice ?? null,
        totalItemsPrice: cartQueryResult.cart?.totalItemsPrice ?? null,
        totalPrice: cartQueryResult.cart?.totalPrice ?? null,
        uuid: cartQueryResult.cart?.uuid ?? null,
        transport:
            transportsQueryResult?.transports.find((transport) => transport.uuid === input.transportUuid) ?? null,
    }) as ChangeTransportInCartMutation['ChangeTransportInCart'];

const getOptimisticChangePaymentInCartResult = (cartQueryResult: CartQuery, input: ChangePaymentInCartInput) => {
    const optimisticPayment = getPaymentFromTransport(cartQueryResult.cart?.transport, input.paymentUuid);

    return {
        __typename: 'Cart',
        items: cartQueryResult.cart?.items ?? null,
        modifications: cartQueryResult.cart?.modifications ?? null,
        payment: optimisticPayment,
        paymentGoPayBankSwift: optimisticPayment === null ? null : cartQueryResult.cart?.paymentGoPayBankSwift ?? null,
        promoCode: cartQueryResult.cart?.promoCode ?? null,
        remainingAmountWithVatForFreeTransport: cartQueryResult.cart?.remainingAmountWithVatForFreeTransport ?? null,
        selectedPickupPlaceIdentifier: cartQueryResult.cart?.selectedPickupPlaceIdentifier ?? null,
        totalDiscountPrice: cartQueryResult.cart?.totalDiscountPrice ?? null,
        totalItemsPrice: cartQueryResult.cart?.totalItemsPrice ?? null,
        totalPrice: cartQueryResult.cart?.totalPrice ?? null,
        uuid: cartQueryResult.cart?.uuid ?? null,
        transport: cartQueryResult.cart?.transport,
    } as ChangePaymentInCartMutation['ChangePaymentInCart'];
};

const getPaymentFromTransport = (
    transport: TransportWithAvailablePaymentsAndStoresFragment | null | undefined,
    paymentUuid: string | null,
) => {
    if (!transport || paymentUuid === null) {
        return null;
    }

    return transport.payments.find((payment) => payment.uuid === paymentUuid) ?? null;
};
