import { invalidateFields } from './cacheUtils';
import { Cache, UpdatesConfig } from '@urql/exchange-graphcache';
import { TypeLoginMutation, TypeLoginMutationVariables } from 'graphql/requests/auth/mutations/LoginMutation.generated';
import {
    TypeLogoutMutation,
    TypeLogoutMutationVariables,
} from 'graphql/requests/auth/mutations/LogoutMutation.generated';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import {
    TypeAddOrderItemsToCartMutation,
    TypeAddOrderItemsToCartMutationVariables,
} from 'graphql/requests/cart/mutations/AddOrderItemsToCartMutation.generated';
import {
    TypeAddToCartMutation,
    TypeAddToCartMutationVariables,
} from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import {
    TypeApplyPromoCodeToCartMutation,
    TypeApplyPromoCodeToCartMutationVariables,
} from 'graphql/requests/cart/mutations/ApplyPromoCodeToCartMutation.generated';
import {
    TypeChangePaymentInCartMutation,
    TypeChangePaymentInCartMutationVariables,
} from 'graphql/requests/cart/mutations/ChangePaymentInCartMutation.generated';
import {
    TypeChangeTransportInCartMutation,
    TypeChangeTransportInCartMutationVariables,
} from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.generated';
import {
    TypeRemoveFromCartMutation,
    TypeRemoveFromCartMutationVariables,
} from 'graphql/requests/cart/mutations/RemoveFromCartMutation.generated';
import {
    TypeRemovePromoCodeFromCartMutation,
    TypeRemovePromoCodeFromCartMutationVariables,
} from 'graphql/requests/cart/mutations/RemovePromoCodeFromCartMutation.generated';
import {
    TypeCartQuery,
    TypeCartQueryVariables,
    CartQueryDocument,
} from 'graphql/requests/cart/queries/CartQuery.generated';
import {
    TypeDeleteDeliveryAddressMutation,
    TypeDeleteDeliveryAddressMutationVariables,
} from 'graphql/requests/customer/mutations/DeleteDeliveryAddressMutation.generated';
import {
    TypeCreateOrderMutation,
    TypeCreateOrderMutationVariables,
} from 'graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { TypeProductListFragment } from 'graphql/requests/productLists/fragments/ProductListFragment.generated';
import {
    TypeAddProductToListMutation,
    TypeAddProductToListMutationVariables,
} from 'graphql/requests/productLists/mutations/AddProductToListMutation.generated';
import {
    TypeRemoveProductFromListMutation,
    TypeRemoveProductFromListMutationVariables,
} from 'graphql/requests/productLists/mutations/RemoveProductFromListMutation.generated';
import {
    TypeRemoveProductListMutation,
    TypeRemoveProductListMutationVariables,
} from 'graphql/requests/productLists/mutations/RemoveProductListMutation.generated';
import {
    TypeProductListQuery,
    TypeProductListQueryVariables,
    ProductListQueryDocument,
} from 'graphql/requests/productLists/queries/ProductListQuery.generated';
import { MakeMaybe, TypeProductListInput } from 'graphql/types';

export const cacheUpdates: UpdatesConfig = {
    Mutation: {
        Login(_result: TypeLoginMutation, _args: TypeLoginMutationVariables, cache) {
            invalidateFields(cache, ['cart']);
        },
        Logout(_result: TypeLogoutMutation, _args: TypeLogoutMutationVariables, cache) {
            invalidateFields(cache, ['cart']);
        },
        DeleteDeliveryAddress(
            _result: TypeDeleteDeliveryAddressMutation,
            _args: TypeDeleteDeliveryAddressMutationVariables,
            cache,
        ) {
            invalidateFields(cache, ['currentCustomerUser']);
        },
        CreateOrder(_result: TypeCreateOrderMutation, _args: TypeCreateOrderMutationVariables, cache) {
            invalidateFields(cache, ['currentCustomerUser']);
        },
        CreateDeliveryAddress(_result: TypeCreateOrderMutation, _args: TypeCreateOrderMutationVariables, cache) {
            invalidateFields(cache, ['currentCustomerUser']);
        },
        AddToCart(result: TypeAddToCartMutation, _args: TypeAddToCartMutationVariables, cache) {
            manuallyUpdateCartQuery(cache, result.AddToCart.cart, result.AddToCart.cart.uuid);
        },
        AddOrderItemsToCart(
            result: TypeAddOrderItemsToCartMutation,
            _args: TypeAddOrderItemsToCartMutationVariables,
            cache,
        ) {
            manuallyUpdateCartQuery(cache, result.AddOrderItemsToCart, result.AddOrderItemsToCart.uuid);
        },
        // Because we use dedup on this mutation, if the mutation is cancelled
        // mid-flight, it calls this updater with the resulting object being null,
        // even though it should not happen
        ChangeTransportInCart(
            result: MakeMaybe<TypeChangeTransportInCartMutation, 'ChangeTransportInCart'>,
            _args: TypeChangeTransportInCartMutationVariables,
            cache,
        ) {
            if (result.ChangeTransportInCart) {
                manuallyUpdateCartQuery(cache, result.ChangeTransportInCart, result.ChangeTransportInCart.uuid);
            }
        },
        // Because we use dedup on this mutation, if the mutation is cancelled
        // mid-flight, it calls this updater with the resulting object being null,
        // even though it should not happen
        ChangePaymentInCart(
            result: MakeMaybe<TypeChangePaymentInCartMutation, 'ChangePaymentInCart'>,
            _args: TypeChangePaymentInCartMutationVariables,
            cache,
        ) {
            if (result.ChangePaymentInCart) {
                manuallyUpdateCartQuery(cache, result.ChangePaymentInCart, result.ChangePaymentInCart.uuid);
            }
        },
        RemoveFromCart(result: TypeRemoveFromCartMutation, _args: TypeRemoveFromCartMutationVariables, cache) {
            manuallyUpdateCartQuery(cache, result.RemoveFromCart, result.RemoveFromCart.uuid);
        },
        ApplyPromoCodeToCart(
            result: TypeApplyPromoCodeToCartMutation,
            _args: TypeApplyPromoCodeToCartMutationVariables,
            cache,
        ) {
            manuallyUpdateCartQuery(cache, result.ApplyPromoCodeToCart, result.ApplyPromoCodeToCart.uuid);
        },
        RemovePromoCodeFromCart(
            result: TypeRemovePromoCodeFromCartMutation,
            _args: TypeRemovePromoCodeFromCartMutationVariables,
            cache,
        ) {
            manuallyUpdateCartQuery(cache, result.RemovePromoCodeFromCart, result.RemovePromoCodeFromCart.uuid);
        },
        AddProductToList(result: TypeAddProductToListMutation, args: TypeAddProductToListMutationVariables, cache) {
            manuallyUpdateProductListQuery(args.input.productListInput, result.AddProductToList, cache);
        },
        RemoveProductFromList(
            result: TypeRemoveProductFromListMutation,
            args: TypeRemoveProductFromListMutationVariables,
            cache,
        ) {
            if (result.RemoveProductFromList === null) {
                manuallyRemoveProductListQuery(cache, args.input.productListInput);
            } else {
                manuallyUpdateProductListQuery(args.input.productListInput, result.RemoveProductFromList, cache);
            }
        },
        RemoveProductList(_result: TypeRemoveProductListMutation, args: TypeRemoveProductListMutationVariables, cache) {
            manuallyRemoveProductListQuery(cache, args.input);
        },
        ChangePaymentInOrder(_result, _args, cache) {
            invalidateFields(cache, ['order']);
        },
    },
};

const manuallyUpdateCartQuery = (cache: Cache, newCart: TypeCartFragment, cartUuid: string | null) => {
    cache.updateQuery<TypeCartQuery, TypeCartQueryVariables>(
        { query: CartQueryDocument, variables: { cartUuid } },
        () => ({
            __typename: 'Query',
            cart: newCart,
        }),
    );
};

const manuallyRemoveProductListQuery = (cache: Cache, args: TypeProductListInput) => {
    cache.updateQuery<TypeProductListQuery, TypeProductListQueryVariables>(
        { query: ProductListQueryDocument, variables: { input: args } },
        () => ({ __typename: 'Query', productList: null }),
    );
};

const manuallyUpdateProductListQuery = (input: TypeProductListInput, result: TypeProductListFragment, cache: Cache) => {
    const uuid = input.uuid ?? result.uuid;
    cache.updateQuery<TypeProductListQuery, TypeProductListQueryVariables>(
        {
            query: ProductListQueryDocument,
            variables: {
                input: { type: input.type, uuid },
            },
        },
        () => ({ __typename: 'Query', productList: result }),
    );
};
