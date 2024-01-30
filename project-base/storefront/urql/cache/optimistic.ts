import { OptimisticMutationConfig } from '@urql/exchange-graphcache';
import {
    ChangeTransportInCartInputApi,
    CartQueryApi,
    CartQueryDocumentApi,
    CartQueryVariablesApi,
    TransportsQueryApi,
    TransportsQueryDocumentApi,
    TransportsQueryVariablesApi,
    ChangePaymentInCartInputApi,
    ChangePaymentInCartMutationApi,
    ChangeTransportInCartMutationApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    ChangeTransportInCartMutationVariablesApi,
    ChangePaymentInCartMutationVariablesApi,
} from 'graphql/generated';

export const optimisticUpdates: OptimisticMutationConfig = {
    ChangeTransportInCart: ({ input }: ChangeTransportInCartMutationVariablesApi, cache) => {
        const cartQueryResult: CartQueryApi | null = cache.readQuery<CartQueryApi, CartQueryVariablesApi>({
            query: CartQueryDocumentApi,
            variables: {
                cartUuid: input.cartUuid ?? null,
            },
        });

        const transportsQueryResult = cache.readQuery<TransportsQueryApi, TransportsQueryVariablesApi>({
            query: TransportsQueryDocumentApi,
            variables: {
                cartUuid: input.cartUuid ?? null,
            },
        });

        if (cartQueryResult === null) {
            return null;
        }

        return getOptimisticChangeTransportInCartResult(cartQueryResult, transportsQueryResult, input);
    },
    ChangePaymentInCart: ({ input }: ChangePaymentInCartMutationVariablesApi, cache) => {
        const cartQueryResult: CartQueryApi | null = cache.readQuery<CartQueryApi, CartQueryVariablesApi>({
            query: CartQueryDocumentApi,
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
    cartQueryResult: CartQueryApi,
    transportsQueryResult: TransportsQueryApi | null,
    input: ChangeTransportInCartInputApi,
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
    }) as ChangeTransportInCartMutationApi['ChangeTransportInCart'];

const getOptimisticChangePaymentInCartResult = (cartQueryResult: CartQueryApi, input: ChangePaymentInCartInputApi) => {
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
    } as ChangePaymentInCartMutationApi['ChangePaymentInCart'];
};

const getPaymentFromTransport = (
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi | null | undefined,
    paymentUuid: string | null,
) => {
    if (!transport || paymentUuid === null) {
        return null;
    }

    return transport.payments.find((payment) => payment.uuid === paymentUuid) ?? null;
};
