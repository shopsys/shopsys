import { invalidateFields } from './helpers';
import { Cache, DataField, UpdatesConfig } from '@urql/exchange-graphcache';
import {
    AddToCartMutationVariablesApi,
    AddToCartResultApi,
    ApplyPromoCodeToCartMutationVariablesApi,
    CartApi,
    CartQueryDocumentApi,
    ChangePaymentInCartMutationVariablesApi,
    ChangeTransportInCartMutationVariablesApi,
    RemoveFromCartMutationVariablesApi,
    RemovePromoCodeFromCartMutationVariablesApi,
    WishlistQueryDocumentApi,
    AddOrderItemsToCartMutationVariablesApi,
    CleanProductListMutationVariablesApi,
    ProductListInputApi,
    ProductListUpdateInputApi,
    ProductListTypeEnumApi,
    ComparisonQueryDocumentApi,
} from 'graphql/generated';

export const cacheUpdates: UpdatesConfig = {
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
        AddToCart(result, args: AddToCartMutationVariablesApi, cache) {
            const addToCartResult =
                typeof result.AddToCart !== 'undefined' ? (result.AddToCart as AddToCartResultApi) : undefined;
            manuallyUpdateCartFragment(cache, addToCartResult?.cart, addToCartResult?.cart.uuid || null);
        },
        AddOrderItemsToCart(result, args: AddOrderItemsToCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.AddOrderItemsToCart !== 'undefined' ? (result.AddOrderItemsToCart as CartApi) : undefined;
            manuallyUpdateCartFragment(cache, newCart, newCart?.uuid || null);
        },
        ChangeTransportInCart(result, args: ChangeTransportInCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.ChangeTransportInCart !== 'undefined'
                    ? (result.ChangeTransportInCart as CartApi)
                    : undefined;
            manuallyUpdateCartFragment(cache, newCart, args.input.cartUuid);
        },
        ChangePaymentInCart(result, args: ChangePaymentInCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.ChangePaymentInCart !== 'undefined' ? (result.ChangePaymentInCart as CartApi) : undefined;
            manuallyUpdateCartFragment(cache, newCart, args.input.cartUuid);
        },
        RemoveFromCart(result, args: RemoveFromCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.RemoveFromCart !== 'undefined' ? (result.RemoveFromCart as CartApi) : undefined;
            manuallyUpdateCartFragment(cache, newCart, args.input.cartUuid);
        },
        ApplyPromoCodeToCart(result, args: ApplyPromoCodeToCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.ApplyPromoCodeToCart !== 'undefined'
                    ? (result.ApplyPromoCodeToCart as CartApi)
                    : undefined;
            manuallyUpdateCartFragment(cache, newCart, args.input.cartUuid);
        },
        RemovePromoCodeFromCart(result, args: RemovePromoCodeFromCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.RemovePromoCodeFromCart !== 'undefined'
                    ? (result.RemovePromoCodeFromCart as CartApi)
                    : undefined;
            manuallyUpdateCartFragment(cache, newCart, args.input.cartUuid);
        },
        AddProductToList(result, args: { input: ProductListUpdateInputApi }, cache) {
            manuallyUpdateProductListQuery(args.input.productListInput, result.AddProductToList, cache);
        },
        RemoveProductFromList(result, args: { input: ProductListUpdateInputApi }, cache) {
            if (result.RemoveProductFromList === null) {
                manuallyRemoveProductList(cache, args.input.productListInput);
            } else {
                manuallyUpdateProductListQuery(args.input.productListInput, result.AddProductToList, cache);
            }
        },
        CleanProductList(_result, args: CleanProductListMutationVariablesApi, cache) {
            manuallyRemoveProductList(cache, args.input);
        },
    },
};

const manuallyUpdateCartFragment = (cache: Cache, newCart: CartApi | undefined, cartUuid: string | null) => {
    if (newCart) {
        cache.updateQuery({ query: CartQueryDocumentApi, variables: { cartUuid } }, (data) => {
            const updatedData = data || { __typename: 'Cart', cart: null };
            updatedData.cart = newCart;

            return updatedData;
        });
    }
};

const manuallyRemoveProductList = (cache: Cache, args: ProductListInputApi) => {
    const query =
        args.type === ProductListTypeEnumApi.WishlistApi ? WishlistQueryDocumentApi : ComparisonQueryDocumentApi;

    cache.updateQuery({ query: query, variables: { input: args } }, (data) => ({
        ...data,
        __typename: 'ProductList',
        productList: null,
    }));
};

const manuallyUpdateProductListQuery = (input: ProductListInputApi, result: DataField, cache: Cache) => {
    const query =
        input.type === ProductListTypeEnumApi.WishlistApi ? WishlistQueryDocumentApi : ComparisonQueryDocumentApi;

    if (typeof result === 'object' && result && 'uuid' in result) {
        const uuid = input.uuid ?? result.uuid;
        cache.updateQuery(
            {
                query: query,
                variables: {
                    input: { type: input.type, uuid },
                },
            },
            () => ({
                __typename: 'ProductList',
                productList: result,
            }),
        );
    }
};
