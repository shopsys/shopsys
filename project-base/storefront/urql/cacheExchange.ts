import { Cache, cacheExchange, Data } from '@urql/exchange-graphcache';
import { IntrospectionQuery } from 'graphql';
import {
    AddToCartResultApi,
    CartApi,
    CartQueryApi,
    CartQueryDocumentApi,
    CartQueryVariablesApi,
    ChangePaymentInCartInputApi,
    ChangePaymentInCartMutationApi,
    ChangeTransportInCartInputApi,
    ChangeTransportInCartMutationApi,
    CleanComparisonMutationVariablesApi,
    CleanWishlistMutationVariablesApi,
    ComparisonQueryApi,
    ComparisonQueryDocumentApi,
    ComparisonQueryVariablesApi,
    Maybe,
    RemoveProductFromComparisonMutationVariablesApi,
    RemoveProductFromWishlistMutationVariablesApi,
    Scalars,
    TransportsQueryApi,
    TransportsQueryDocumentApi,
    TransportsQueryVariablesApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    WishlistQueryApi,
    WishlistQueryDocumentApi,
    WishlistQueryVariablesApi,
} from 'graphql/generated';
import schema from 'schema.graphql.json';

const keyNull = () => null;
const keyWishlist = () => 'wishlist';
const keyUuid = (data: Data) => data.uuid as string | null;
const keyName = (data: Data) => data.name as string | null;
const keyCode = (data: Data) => data.code as string | null;
const keyUrl = (data: Data) => data.url as string | null;
const keyComparison = () => 'comparison';

export const cache = cacheExchange({
    schema: schema as unknown as IntrospectionQuery,
    keys: {
        AdditionalSize: keyUrl,
        Advert: keyUuid,
        AdvertCode: keyUuid,
        AdvertImage: keyUuid,
        AdvertPosition: (data) => data.positionName as string | null,
        ArticleSite: keyUuid,
        Availability: keyName,
        BlogArticle: keyUuid,
        BlogCategory: keyUuid,
        Brand: keyUuid,
        BrandFilterOption: keyNull,
        Cart: keyUuid,
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
        Parameter: keyNull,
        ParameterCheckboxFilterOption: keyNull,
        ParameterSliderFilterOption: keyNull,
        ParameterColorFilterOption: keyNull,
        ParameterValueColorFilterOption: keyNull,
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
        Comparison: keyComparison,
        RegularCustomerUser: keyUuid,
        RegularProduct: keyUuid,
        SeoSetting: keyNull,
        SeoPage: keyNull,
        Settings: keyNull,
        SliderItem: keyUuid,
        Store: keyUuid,
        StoreAvailability: keyNull,
        Transport: keyUuid,
        TransportType: keyCode,
        Unit: keyName,
        Variant: keyUuid,
        Wishlist: keyWishlist,
    },
    updates: {
        Mutation: {
            Login(_result, _args, cache) {
                invalidateFields(cache, ['cart', 'wishlist']);
            },
            Logout(_result, _args, cache) {
                invalidateFields(cache, ['cart', 'wishlist']);
            },
            DeleteDeliveryAddress(_result, _args, cache) {
                invalidateFields(cache, ['currentCustomerUser']);
            },
            CreateOrder(_result, _args, cache) {
                invalidateFields(cache, ['currentCustomerUser']);
            },
            AddToCart(result, _args, cache) {
                const newCart =
                    typeof result.AddToCart !== 'undefined' ? (result.AddToCart as AddToCartResultApi) : undefined;
                manuallyUpdateCartFragment(cache, newCart?.cart);
            },
            ChangeTransportInCart(result, _args, cache) {
                const newCart =
                    typeof result.ChangeTransportInCart !== 'undefined'
                        ? (result.ChangeTransportInCart as CartApi)
                        : undefined;
                manuallyUpdateCartFragment(cache, newCart);
            },
            ChangePaymentInCart(result, _args, cache) {
                const newCart =
                    typeof result.ChangePaymentInCart !== 'undefined'
                        ? (result.ChangePaymentInCart as CartApi)
                        : undefined;
                manuallyUpdateCartFragment(cache, newCart);
            },
            RemoveFromCart(result, _args, cache) {
                const newCart =
                    typeof result.RemoveFromCart !== 'undefined' ? (result.RemoveFromCart as CartApi) : undefined;
                manuallyUpdateCartFragment(cache, newCart);
            },
            ApplyPromoCodeToCart(result, _args, cache) {
                const newCart =
                    typeof result.ApplyPromoCodeToCart !== 'undefined'
                        ? (result.ApplyPromoCodeToCart as CartApi)
                        : undefined;
                manuallyUpdateCartFragment(cache, newCart);
            },
            RemovePromoCodeFromCart(result, _args, cache) {
                const newCart =
                    typeof result.RemovePromoCodeFromCart !== 'undefined'
                        ? (result.RemovePromoCodeFromCart as CartApi)
                        : undefined;
                manuallyUpdateCartFragment(cache, newCart);
            },
            removeProductFromComparison(result, args: RemoveProductFromComparisonMutationVariablesApi, cache) {
                if (result.removeProductFromComparison === null) {
                    clearComparisonQueryFragment(cache, args.comparisonUuid);
                }
            },
            cleanComparison(result, args: CleanComparisonMutationVariablesApi, cache) {
                clearComparisonQueryFragment(cache, args.comparisonUuid);
            },
            removeProductFromWishlist(result, args: RemoveProductFromWishlistMutationVariablesApi, cache) {
                if (result.removeProductFromWishlist === null) {
                    clearWishlistQueryFragment(cache, args.wishlistUuid);
                }
            },
            cleanWishlist(result, args: CleanWishlistMutationVariablesApi, cache) {
                clearWishlistQueryFragment(cache, args.wishlistUuid);
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

const manuallyUpdateCartFragment = (cache: Cache, newCart: CartApi | undefined) => {
    if (newCart !== undefined) {
        cache.updateQuery<CartQueryApi, CartQueryVariablesApi>(
            { query: CartQueryDocumentApi, variables: { cartUuid: newCart.uuid } },
            (data) => {
                if (typeof newCart !== 'undefined') {
                    // eslint-disable-next-line no-param-reassign
                    data = {
                        __typename: 'Query',
                        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
                        // @ts-ignore
                        cart: newCart,
                    };
                }

                return data;
            },
        );
    }
};

const clearComparisonQueryFragment = (cache: Cache, comparisonUuid: Maybe<Scalars['Uuid']>) => {
    cache.updateQuery<ComparisonQueryApi, ComparisonQueryVariablesApi>(
        { query: ComparisonQueryDocumentApi, variables: { comparisonUuid } },
        () => {
            return {
                __typename: 'Query',
                comparison: null,
            };
        },
    );
};

const clearWishlistQueryFragment = (cache: Cache, wishlistUuid: Maybe<Scalars['Uuid']>) => {
    cache.updateQuery<WishlistQueryApi, WishlistQueryVariablesApi>(
        { query: WishlistQueryDocumentApi, variables: { wishlistUuid } },
        () => {
            return {
                __typename: 'Query',
                wishlist: null,
            };
        },
    );
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
