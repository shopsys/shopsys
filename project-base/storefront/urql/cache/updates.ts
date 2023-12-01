import { invalidateFields } from './helpers';
import { Cache, UpdatesConfig } from '@urql/exchange-graphcache';
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
    AddOrderItemsToCartMutationVariablesApi,
    RemoveProductListMutationVariablesApi,
    ProductListInputApi,
    RemoveProductFromListMutationVariablesApi,
    AddProductToListMutationVariablesApi,
    ProductListQueryDocumentApi,
    AddProductToListMutationApi,
    RemoveProductFromListMutationApi,
    ProductListFragmentApi,
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
            manuallyUpdateCartQuery(cache, addToCartResult?.cart, addToCartResult?.cart.uuid || null);
        },
        AddOrderItemsToCart(result, args: AddOrderItemsToCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.AddOrderItemsToCart !== 'undefined' ? (result.AddOrderItemsToCart as CartApi) : undefined;
            manuallyUpdateCartQuery(cache, newCart, newCart?.uuid || null);
        },
        ChangeTransportInCart(result, args: ChangeTransportInCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.ChangeTransportInCart !== 'undefined'
                    ? (result.ChangeTransportInCart as CartApi)
                    : undefined;
            manuallyUpdateCartQuery(cache, newCart, args.input.cartUuid);
        },
        ChangePaymentInCart(result, args: ChangePaymentInCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.ChangePaymentInCart !== 'undefined' ? (result.ChangePaymentInCart as CartApi) : undefined;
            manuallyUpdateCartQuery(cache, newCart, args.input.cartUuid);
        },
        RemoveFromCart(result, args: RemoveFromCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.RemoveFromCart !== 'undefined' ? (result.RemoveFromCart as CartApi) : undefined;
            manuallyUpdateCartQuery(cache, newCart, args.input.cartUuid);
        },
        ApplyPromoCodeToCart(result, args: ApplyPromoCodeToCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.ApplyPromoCodeToCart !== 'undefined'
                    ? (result.ApplyPromoCodeToCart as CartApi)
                    : undefined;
            manuallyUpdateCartQuery(cache, newCart, args.input.cartUuid);
        },
        RemovePromoCodeFromCart(result, args: RemovePromoCodeFromCartMutationVariablesApi, cache) {
            const newCart =
                typeof result.RemovePromoCodeFromCart !== 'undefined'
                    ? (result.RemovePromoCodeFromCart as CartApi)
                    : undefined;
            manuallyUpdateCartQuery(cache, newCart, args.input.cartUuid);
        },
        AddProductToList(result: AddProductToListMutationApi, args: AddProductToListMutationVariablesApi, cache) {
            manuallyUpdateProductListQuery(args.input.productListInput, result.AddProductToList, cache);
        },
        RemoveProductFromList(
            result: RemoveProductFromListMutationApi,
            args: RemoveProductFromListMutationVariablesApi,
            cache,
        ) {
            if (result.RemoveProductFromList === null) {
                manuallyRemoveProductListQuery(cache, args.input.productListInput);
            } else {
                manuallyUpdateProductListQuery(args.input.productListInput, result.RemoveProductFromList, cache);
            }
        },
        RemoveProductList(_result, args: RemoveProductListMutationVariablesApi, cache) {
            manuallyRemoveProductListQuery(cache, args.input);
        },
    },
};

const manuallyUpdateCartQuery = (cache: Cache, newCart: CartApi | undefined, cartUuid: string | null) => {
    if (newCart) {
        cache.updateQuery({ query: CartQueryDocumentApi, variables: { cartUuid } }, (data) => {
            const updatedData = data || { __typename: 'Cart', cart: null };
            updatedData.cart = newCart;

            return updatedData;
        });
    }
};

const manuallyRemoveProductListQuery = (cache: Cache, args: ProductListInputApi) => {
    cache.updateQuery({ query: ProductListQueryDocumentApi, variables: { input: args } }, () => ({
        __typename: 'ProductList',
        productList: null,
    }));
};

const manuallyUpdateProductListQuery = (input: ProductListInputApi, result: ProductListFragmentApi, cache: Cache) => {
    const uuid = input.uuid ?? result.uuid;
    cache.updateQuery(
        {
            query: ProductListQueryDocumentApi,
            variables: {
                input: { type: input.type, uuid },
            },
        },
        () => ({
            __typename: 'ProductList',
            productList: result,
        }),
    );
};
