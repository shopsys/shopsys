import { OptimisticMutationConfig } from '@urql/exchange-graphcache';
import {
    TypeChangePaymentInCartMutationVariables,
    TypeChangePaymentInCartMutation,
} from 'graphql/requests/cart/mutations/ChangePaymentInCartMutation.ssr';
import {
    TypeChangeTransportInCartMutationVariables,
    TypeChangeTransportInCartMutation,
} from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.ssr';
import { TypeCartQuery, TypeCartQueryVariables, CartQueryDocument } from 'graphql/requests/cart/queries/CartQuery.ssr';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.ssr';
import {
    TypeTransportsQuery,
    TypeTransportsQueryVariables,
    TransportsQueryDocument,
} from 'graphql/requests/transports/queries/TransportsQuery.ssr';
import { TypeChangeTransportInCartInput, TypeChangePaymentInCartInput } from 'graphql/types';

export const optimisticUpdates: OptimisticMutationConfig = {
    ChangeTransportInCart: ({ input }: TypeChangeTransportInCartMutationVariables, cache) => {
        const cartQueryResult: TypeCartQuery | null = cache.readQuery<TypeCartQuery, TypeCartQueryVariables>({
            query: CartQueryDocument,
            variables: {
                cartUuid: input.cartUuid ?? null,
            },
        });

        const transportsQueryResult = cache.readQuery<TypeTransportsQuery, TypeTransportsQueryVariables>({
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
    ChangePaymentInCart: ({ input }: TypeChangePaymentInCartMutationVariables, cache) => {
        const cartQueryResult: TypeCartQuery | null = cache.readQuery<TypeCartQuery, TypeCartQueryVariables>({
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
    cartQueryResult: TypeCartQuery,
    transportsQueryResult: TypeTransportsQuery | null,
    input: TypeChangeTransportInCartInput,
) =>
    ({
        __typename: 'Cart',
        items: cartQueryResult.cart?.items ?? null,
        totalPrice: cartQueryResult.cart?.totalPrice,
        totalItemsPrice: cartQueryResult.cart?.totalItemsPrice,
        totalDiscountPrice: cartQueryResult.cart?.totalDiscountPrice,
        uuid: cartQueryResult.cart?.uuid ?? null,
        selectedPickupPlaceIdentifier: cartQueryResult.cart?.selectedPickupPlaceIdentifier ?? null,
        paymentGoPayBankSwift: cartQueryResult.cart?.paymentGoPayBankSwift ?? null,
        remainingAmountForFreeTransport: cartQueryResult.cart?.remainingAmountForFreeTransport ?? null,
        roundingPrice: cartQueryResult.cart?.roundingPrice ?? null,
        modifications: cartQueryResult.cart?.modifications,
        payment: cartQueryResult.cart?.payment,
        promoCodes: cartQueryResult.cart?.promoCodes ?? [],
        transport:
            transportsQueryResult?.transports.find((transport) => transport.uuid === input.transportUuid) ?? null,
    }) as unknown as TypeChangeTransportInCartMutation['ChangeTransportInCart'];

const getOptimisticChangePaymentInCartResult = (
    cartQueryResult: TypeCartQuery,
    input: TypeChangePaymentInCartInput,
) => {
    const optimisticPayment = getPaymentFromTransport(cartQueryResult.cart?.transport, input.paymentUuid);

    return {
        __typename: 'Cart',
        items: cartQueryResult.cart?.items ?? null,
        totalPrice: cartQueryResult.cart?.totalPrice,
        totalItemsPrice: cartQueryResult.cart?.totalItemsPrice,
        totalDiscountPrice: cartQueryResult.cart?.totalDiscountPrice,
        uuid: cartQueryResult.cart?.uuid ?? null,
        selectedPickupPlaceIdentifier: cartQueryResult.cart?.selectedPickupPlaceIdentifier ?? null,
        paymentGoPayBankSwift: optimisticPayment === null ? null : cartQueryResult.cart?.paymentGoPayBankSwift ?? null,
        remainingAmountForFreeTransport: cartQueryResult.cart?.remainingAmountForFreeTransport ?? null,
        roundingPrice: cartQueryResult.cart?.roundingPrice ?? null,
        modifications: cartQueryResult.cart?.modifications,
        payment: optimisticPayment,
        promoCodes: cartQueryResult.cart?.promoCodes ?? [],
        transport: cartQueryResult.cart?.transport,
    } as unknown as TypeChangePaymentInCartMutation['ChangePaymentInCart'];
};

const getPaymentFromTransport = (
    transport: TypeTransportWithAvailablePaymentsAndStoresFragment | null | undefined,
    paymentUuid: string | null,
) => {
    if (!transport || paymentUuid === null) {
        return null;
    }

    return transport.payments.find((payment) => payment.uuid === paymentUuid) ?? null;
};
