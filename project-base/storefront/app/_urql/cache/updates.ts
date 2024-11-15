import { invalidateFields } from './cacheUtils';
import { Cache, UpdatesConfig } from '@urql/exchange-graphcache';
import { TypeLoginMutation, TypeLoginMutationVariables } from 'graphql/requests/auth/mutations/LoginMutation.ssr';
import { TypeLogoutMutation, TypeLogoutMutationVariables } from 'graphql/requests/auth/mutations/LogoutMutation.ssr';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.ssr';
import {
    TypeAddOrderItemsToCartMutation,
    TypeAddOrderItemsToCartMutationVariables,
} from 'graphql/requests/cart/mutations/AddOrderItemsToCartMutation.ssr';
import {
    TypeAddToCartMutation,
    TypeAddToCartMutationVariables,
} from 'graphql/requests/cart/mutations/AddToCartMutation.ssr';
import {
    TypeApplyPromoCodeToCartMutation,
    TypeApplyPromoCodeToCartMutationVariables,
} from 'graphql/requests/cart/mutations/ApplyPromoCodeToCartMutation.ssr';
import {
    TypeChangePaymentInCartMutation,
    TypeChangePaymentInCartMutationVariables,
} from 'graphql/requests/cart/mutations/ChangePaymentInCartMutation.ssr';
import {
    TypeChangeTransportInCartMutation,
    TypeChangeTransportInCartMutationVariables,
} from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.ssr';
import {
    TypeRemoveFromCartMutation,
    TypeRemoveFromCartMutationVariables,
} from 'graphql/requests/cart/mutations/RemoveFromCartMutation.ssr';
import {
    TypeRemovePromoCodeFromCartMutation,
    TypeRemovePromoCodeFromCartMutationVariables,
} from 'graphql/requests/cart/mutations/RemovePromoCodeFromCartMutation.ssr';
import { TypeCartQuery, TypeCartQueryVariables, CartQueryDocument } from 'graphql/requests/cart/queries/CartQuery.ssr';
import {
    TypeDeleteDeliveryAddressMutation,
    TypeDeleteDeliveryAddressMutationVariables,
} from 'graphql/requests/customer/mutations/DeleteDeliveryAddressMutation.ssr';
import {
    TypeCreateOrderMutation,
    TypeCreateOrderMutationVariables,
} from 'graphql/requests/orders/mutations/CreateOrderMutation.ssr';
import { TypeProductListFragment } from 'graphql/requests/productLists/fragments/ProductListFragment.ssr';
import {
    TypeAddProductToListMutation,
    TypeAddProductToListMutationVariables,
} from 'graphql/requests/productLists/mutations/AddProductToListMutation.ssr';
import {
    TypeRemoveProductFromListMutation,
    TypeRemoveProductFromListMutationVariables,
} from 'graphql/requests/productLists/mutations/RemoveProductFromListMutation.ssr';
import {
    TypeRemoveProductListMutation,
    TypeRemoveProductListMutationVariables,
} from 'graphql/requests/productLists/mutations/RemoveProductListMutation.ssr';
import {
    TypeProductListQuery,
    TypeProductListQueryVariables,
    ProductListQueryDocument,
} from 'graphql/requests/productLists/queries/ProductListQuery.ssr';
import { MakeMaybe, TypeProductListInput } from 'graphql/types';

export const cacheUpdates: UpdatesConfig = {
    Mutation: {
        Login(_result: TypeLoginMutation, _args: TypeLoginMutationVariables, cache) {
            invalidateFields(cache, ['cart', 'currentCustomerUser']);
            cache.invalidate('ProductPrice');
        },
        Logout(_result: TypeLogoutMutation, _args: TypeLogoutMutationVariables, cache) {
            invalidateFields(cache, ['cart', 'currentCustomerUser']);
            cache.invalidate('ProductPrice');
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
        AddNewCustomerUser(_result, _args, cache) {
            invalidateFields(cache, ['customerUsers', 'currentCustomerUser']);
        },
        EditCustomerUserPersonalData(_result, _args, cache) {
            invalidateFields(cache, ['customerUsers', 'currentCustomerUser']);
        },
        RemoveCustomerUser(_result, _args, cache) {
            invalidateFields(cache, ['customerUsers', 'currentCustomerUser']);
        },
        CreateComplaint(_result, _args, cache) {
            invalidateFields(cache, ['complaints']);
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
