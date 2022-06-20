import { Cache, cacheExchange, Data } from '@urql/exchange-graphcache';
import { IntrospectionQuery } from 'graphql';
import {
    CartQueryApi,
    CartQueryDocumentApi,
    CartQueryVariablesApi,
    ChangePaymentInCartInputApi,
    ChangePaymentInCartMutationApi,
    ChangeTransportInCartInputApi,
    ChangeTransportInCartMutationApi,
    TransportsQueryApi,
    TransportsQueryDocumentApi,
    TransportsQueryVariablesApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import schema from 'schema.graphql.json';

const keyNull = () => null;
const keyCart = () => 'cart';
const keyUuid = (data: Data) => data.uuid as string | null;
const keyName = (data: Data) => data.name as string | null;
const keyCode = (data: Data) => data.code as string | null;
const keyUrl = (data: Data) => data.url as string | null;

const cache = cacheExchange({
    schema: schema as unknown as IntrospectionQuery,
    keys: {
        AdditionalSize: keyUrl,
        Advert: keyUuid,
        AdvertCode: keyUuid,
        AdvertImage: keyUuid,
        AdvertPosition: (data) => data.positionName as string | null,
        Article: keyUuid,
        Availability: keyName,
        BlogArticle: keyUuid,
        BlogCategory: keyUuid,
        Brand: keyUuid,
        BrandFilterOption: keyNull,
        Cart: keyCart,
        CartItem: keyUuid,
        CartItemModificationsResult: keyNull,
        CartModificationsResult: keyNull,
        CartPaymentModificationsResult: keyNull,
        CartTransportModificationsResult: keyNull,
        Category: keyUuid,
        CompanyCustomerUser: keyUuid,
        Country: keyCode,
        CustomerUser: keyUuid,
        DeliveryAddress: keyUuid,
        File: keyNull,
        Flag: keyUuid,
        FlagFilterOption: keyNull,
        GoPayPaymentMethod: (data) => data.identifier as string | null,
        Image: keyNull,
        ImageSize: keyUrl,
        Link: keyNull,
        MainVariant: keyUuid,
        NavigationItem: keyNull,
        NavigationItemCategoriesByColumns: keyNull,
        NewsletterSubscriber: keyNull,
        NotificationBar: keyNull,
        Order: keyUuid,
        OrderItem: keyNull,
        Parameter: keyUuid,
        ParameterCheckboxFilterOption: keyNull,
        ParameterSliderFilterOption: keyNull,
        ParameterColorFilterOption: keyNull,
        ParameterValue: keyUuid,
        ParameterValueFilterOption: keyNull,
        Payment: keyUuid,
        PersonalData: keyNull,
        PersonalDataPage: keyNull,
        Price: keyNull,
        PricingSetting: keyNull,
        Product: keyUuid,
        ProductFilterOptions: keyNull,
        ProductPrice: keyNull,
        RegularCustomerUser: keyUuid,
        RegularProduct: keyUuid,
        SeoSetting: keyNull,
        Settings: keyNull,
        SliderItem: keyUuid,
        Store: keyUuid,
        StoreAvailability: keyNull,
        Transport: keyUuid,
        TransportType: keyCode,
        Unit: keyName,
        Variant: keyUuid,
    },
    updates: {
        Mutation: {
            Login(_result, _args, cache) {
                invalidateFields(cache, ['cart']);
            },
            Logout(_result, _args, cache) {
                invalidateFields(cache, ['cart']);
            },
            DeleteDeliveryAddress(_result, _args, cache) {
                invalidateFields(cache, ['currentCustomerUser']);
            },
            CreateOrder(_result, _args, cache) {
                invalidateFields(cache, ['currentCustomerUser']);
            },
        },
    },
    optimistic: {
        ChangeTransportInCart: ({ input }: { input: ChangeTransportInCartInputApi }, cache) => {
            const cartQueryResult: CartQueryApi | null = cache.readQuery<CartQueryApi>({
                query: CartQueryDocumentApi,
                variables: {
                    cartUuid: input.cartUuid ?? null,
                } as CartQueryVariablesApi,
            });

            const transportsQueryResult = cache.readQuery<TransportsQueryApi>({
                query: TransportsQueryDocumentApi,
                variables: {
                    cartUuid: input.cartUuid ?? null,
                } as TransportsQueryVariablesApi,
            });

            if (cartQueryResult === null) {
                return null;
            }

            return getOptimisticChangeTransportInCartResult(cartQueryResult, transportsQueryResult, input);
        },
        ChangePaymentInCart: ({ input }: { input: ChangePaymentInCartInputApi }, cache) => {
            const cartQueryResult: CartQueryApi | null = cache.readQuery<CartQueryApi>({
                query: CartQueryDocumentApi,
                variables: {
                    cartUuid: input.cartUuid ?? null,
                } as CartQueryVariablesApi,
            });

            if (cartQueryResult === null) {
                return null;
            }

            return getOptimisticChangePaymentInCartResult(cartQueryResult, input);
        },
    },
});

const invalidateFields = (cache: Cache, fields: string[]): void => {
    const key = 'Query';
    for (const field of cache.inspectFields('Query')) {
        if (fields.includes(field.fieldName)) {
            cache.invalidate(key, field.fieldKey);
        }
    }
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
    } as ChangeTransportInCartMutationApi['ChangeTransportInCart']);

const getOptimisticChangePaymentInCartResult = (cartQueryResult: CartQueryApi, input: ChangePaymentInCartInputApi) =>
    ({
        __typename: 'Cart',
        items: cartQueryResult.cart?.items ?? null,
        modifications: cartQueryResult.cart?.modifications ?? null,
        payment: getPaymentFromTransport(cartQueryResult.cart?.transport, input.paymentUuid),
        paymentGoPayBankSwift: cartQueryResult.cart?.paymentGoPayBankSwift ?? null,
        promoCode: cartQueryResult.cart?.promoCode ?? null,
        remainingAmountWithVatForFreeTransport: cartQueryResult.cart?.remainingAmountWithVatForFreeTransport ?? null,
        selectedPickupPlaceIdentifier: cartQueryResult.cart?.selectedPickupPlaceIdentifier ?? null,
        totalDiscountPrice: cartQueryResult.cart?.totalDiscountPrice ?? null,
        totalItemsPrice: cartQueryResult.cart?.totalItemsPrice ?? null,
        totalPrice: cartQueryResult.cart?.totalPrice ?? null,
        uuid: cartQueryResult.cart?.uuid ?? null,
        transport: cartQueryResult.cart?.transport,
    } as ChangePaymentInCartMutationApi['ChangePaymentInCart']);

const getPaymentFromTransport = (
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi | null | undefined,
    paymentUuid: string | null,
) => {
    if (!transport || paymentUuid === null) {
        return null;
    }

    return transport.payments.find((payment) => payment.uuid === paymentUuid) ?? null;
};

export default cache;
