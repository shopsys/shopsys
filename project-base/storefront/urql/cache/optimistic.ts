import { OptimisticMutationConfig } from '@urql/exchange-graphcache';
import {
    TypeChangePaymentInCartMutationVariables,
    TypeChangePaymentInCartMutation,
} from 'graphql/requests/cart/mutations/ChangePaymentInCartMutation.generated';
import {
    TypeChangeTransportInCartMutationVariables,
    TypeChangeTransportInCartMutation,
} from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.generated';
import {
    TypeCartQuery,
    TypeCartQueryVariables,
    CartQueryDocument,
} from 'graphql/requests/cart/queries/CartQuery.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import {
    TypeTransportsQuery,
    TypeTransportsQueryVariables,
    TransportsQueryDocument,
} from 'graphql/requests/transports/queries/TransportsQuery.generated';
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
        modifications: cartQueryResult.cart?.modifications ?? null,
        payment: cartQueryResult.cart?.payment ?? null,
        paymentGoPayBankSwift: cartQueryResult.cart?.paymentGoPayBankSwift ?? null,
        promoCodes: cartQueryResult.cart?.promoCodes ?? [],
        remainingAmountWithVatForFreeTransport: cartQueryResult.cart?.remainingAmountWithVatForFreeTransport ?? null,
        selectedPickupPlaceIdentifier: input.pickupPlaceIdentifier ?? null,
        totalDiscountPrice: cartQueryResult.cart?.totalDiscountPrice ?? null,
        totalItemsPrice: cartQueryResult.cart?.totalItemsPrice ?? null,
        totalPrice: cartQueryResult.cart?.totalPrice ?? null,
        uuid: cartQueryResult.cart?.uuid ?? null,
        transport:
            transportsQueryResult?.transports.find((transport) => transport.uuid === input.transportUuid) ?? null,
    }) as TypeChangeTransportInCartMutation['ChangeTransportInCart'];

const getOptimisticChangePaymentInCartResult = (
    cartQueryResult: TypeCartQuery,
    input: TypeChangePaymentInCartInput,
) => {
    const optimisticPayment = getPaymentFromTransport(cartQueryResult.cart?.transport, input.paymentUuid);

    return {
        __typename: 'Cart',
        items: cartQueryResult.cart?.items ?? null,
        modifications: cartQueryResult.cart?.modifications ?? null,
        payment: optimisticPayment,
        paymentGoPayBankSwift: optimisticPayment === null ? null : cartQueryResult.cart?.paymentGoPayBankSwift ?? null,
        promoCodes: cartQueryResult.cart?.promoCodes ?? [],
        remainingAmountWithVatForFreeTransport: cartQueryResult.cart?.remainingAmountWithVatForFreeTransport ?? null,
        selectedPickupPlaceIdentifier: cartQueryResult.cart?.selectedPickupPlaceIdentifier ?? null,
        totalDiscountPrice: cartQueryResult.cart?.totalDiscountPrice ?? null,
        totalItemsPrice: cartQueryResult.cart?.totalItemsPrice ?? null,
        totalPrice: cartQueryResult.cart?.totalPrice ?? null,
        uuid: cartQueryResult.cart?.uuid ?? null,
        transport: cartQueryResult.cart?.transport,
    } as TypeChangePaymentInCartMutation['ChangePaymentInCart'];
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
