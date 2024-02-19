import { invalidateFields } from './helpers';
import { Cache, UpdatesConfig } from '@urql/exchange-graphcache';
import { LoginMutation, LoginMutationVariables } from 'graphql/requests/auth/mutations/LoginMutation.generated';
import { LogoutMutation, LogoutMutationVariables } from 'graphql/requests/auth/mutations/LogoutMutation.generated';
import { CartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import {
    AddOrderItemsToCartMutation,
    AddOrderItemsToCartMutationVariables,
} from 'graphql/requests/cart/mutations/AddOrderItemsToCartMutation.generated';
import {
    AddToCartMutation,
    AddToCartMutationVariables,
} from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import {
    ApplyPromoCodeToCartMutation,
    ApplyPromoCodeToCartMutationVariables,
} from 'graphql/requests/cart/mutations/ApplyPromoCodeToCartMutation.generated';
import {
    ChangePaymentInCartMutation,
    ChangePaymentInCartMutationVariables,
} from 'graphql/requests/cart/mutations/ChangePaymentInCartMutation.generated';
import {
    ChangeTransportInCartMutation,
    ChangeTransportInCartMutationVariables,
} from 'graphql/requests/cart/mutations/ChangeTransportInCartMutation.generated';
import {
    RemoveFromCartMutation,
    RemoveFromCartMutationVariables,
} from 'graphql/requests/cart/mutations/RemoveFromCartMutation.generated';
import {
    RemovePromoCodeFromCartMutation,
    RemovePromoCodeFromCartMutationVariables,
} from 'graphql/requests/cart/mutations/RemovePromoCodeFromCartMutation.generated';
import { CartQuery, CartQueryVariables, CartQueryDocument } from 'graphql/requests/cart/queries/CartQuery.generated';
import {
    DeleteDeliveryAddressMutation,
    DeleteDeliveryAddressMutationVariables,
} from 'graphql/requests/customer/mutations/DeleteDeliveryAddressMutation.generated';
import {
    CreateOrderMutation,
    CreateOrderMutationVariables,
} from 'graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { ProductListFragment } from 'graphql/requests/productLists/fragments/ProductListFragment.generated';
import {
    AddProductToListMutation,
    AddProductToListMutationVariables,
} from 'graphql/requests/productLists/mutations/AddProductToListMutation.generated';
import {
    RemoveProductFromListMutation,
    RemoveProductFromListMutationVariables,
} from 'graphql/requests/productLists/mutations/RemoveProductFromListMutation.generated';
import {
    RemoveProductListMutation,
    RemoveProductListMutationVariables,
} from 'graphql/requests/productLists/mutations/RemoveProductListMutation.generated';
import {
    ProductListQuery,
    ProductListQueryVariables,
    ProductListQueryDocument,
} from 'graphql/requests/productLists/queries/ProductListQuery.generated';
import { MakeMaybe, ProductListInput } from 'graphql/types';

export const cacheUpdates: UpdatesConfig = {
    Mutation: {
        Login(_result: LoginMutation, _args: LoginMutationVariables, cache) {
            invalidateFields(cache, ['cart']);
        },
        Logout(_result: LogoutMutation, _args: LogoutMutationVariables, cache) {
            invalidateFields(cache, ['cart']);
        },
        DeleteDeliveryAddress(
            _result: DeleteDeliveryAddressMutation,
            _args: DeleteDeliveryAddressMutationVariables,
            cache,
        ) {
            invalidateFields(cache, ['currentCustomerUser']);
        },
        CreateOrder(_result: CreateOrderMutation, _args: CreateOrderMutationVariables, cache) {
            invalidateFields(cache, ['currentCustomerUser']);
        },
        AddToCart(result: AddToCartMutation, _args: AddToCartMutationVariables, cache) {
            manuallyUpdateCartQuery(cache, result.AddToCart.cart, result.AddToCart.cart.uuid);
        },
        AddOrderItemsToCart(result: AddOrderItemsToCartMutation, _args: AddOrderItemsToCartMutationVariables, cache) {
            manuallyUpdateCartQuery(cache, result.AddOrderItemsToCart, result.AddOrderItemsToCart.uuid);
        },
        // Because we use dedup on this mutation, if the mutation is cancelled
        // mid-flight, it calls this updater with the resulting object being null,
        // even though it should not happen
        ChangeTransportInCart(
            result: MakeMaybe<ChangeTransportInCartMutation, 'ChangeTransportInCart'>,
            _args: ChangeTransportInCartMutationVariables,
            cache,
        ) {
            if (result.ChangeTransportInCart?.uuid) {
                manuallyUpdateCartQuery(cache, result.ChangeTransportInCart, result.ChangeTransportInCart.uuid);
            }
        },
        // Because we use dedup on this mutation, if the mutation is cancelled
        // mid-flight, it calls this updater with the resulting object being null,
        // even though it should not happen
        ChangePaymentInCart(
            result: MakeMaybe<ChangePaymentInCartMutation, 'ChangePaymentInCart'>,
            _args: ChangePaymentInCartMutationVariables,
            cache,
        ) {
            if (result.ChangePaymentInCart?.uuid) {
                manuallyUpdateCartQuery(cache, result.ChangePaymentInCart, result.ChangePaymentInCart.uuid);
            }
        },
        RemoveFromCart(result: RemoveFromCartMutation, _args: RemoveFromCartMutationVariables, cache) {
            manuallyUpdateCartQuery(cache, result.RemoveFromCart, result.RemoveFromCart.uuid);
        },
        ApplyPromoCodeToCart(
            result: ApplyPromoCodeToCartMutation,
            _args: ApplyPromoCodeToCartMutationVariables,
            cache,
        ) {
            manuallyUpdateCartQuery(cache, result.ApplyPromoCodeToCart, result.ApplyPromoCodeToCart.uuid);
        },
        RemovePromoCodeFromCart(
            result: RemovePromoCodeFromCartMutation,
            _args: RemovePromoCodeFromCartMutationVariables,
            cache,
        ) {
            manuallyUpdateCartQuery(cache, result.RemovePromoCodeFromCart, result.RemovePromoCodeFromCart.uuid);
        },
        AddProductToList(result: AddProductToListMutation, args: AddProductToListMutationVariables, cache) {
            cache.invalidate('Query');
            manuallyUpdateProductListQuery(args.input.productListInput, result.AddProductToList, cache);
        },
        RemoveProductFromList(
            result: RemoveProductFromListMutation,
            args: RemoveProductFromListMutationVariables,
            cache,
        ) {
            if (result.RemoveProductFromList === null) {
                manuallyRemoveProductListQuery(cache, args.input.productListInput);
            } else {
                manuallyUpdateProductListQuery(args.input.productListInput, result.RemoveProductFromList, cache);
            }
        },
        RemoveProductList(_result: RemoveProductListMutation, args: RemoveProductListMutationVariables, cache) {
            manuallyRemoveProductListQuery(cache, args.input);
        },
        ChangePaymentInOrder(_result, _args, cache) {
            invalidateFields(cache, ['order']);
        },
    },
};

const manuallyUpdateCartQuery = (cache: Cache, newCart: CartFragment, cartUuid: string | null) => {
    cache.updateQuery<CartQuery, CartQueryVariables>({ query: CartQueryDocument, variables: { cartUuid } }, () => ({
        __typename: 'Query',
        cart: newCart,
    }));
};

const manuallyRemoveProductListQuery = (cache: Cache, args: ProductListInput) => {
    cache.updateQuery<ProductListQuery, ProductListQueryVariables>(
        { query: ProductListQueryDocument, variables: { input: args } },
        () => ({ __typename: 'Query', productList: null }),
    );
};

const manuallyUpdateProductListQuery = (input: ProductListInput, result: ProductListFragment, cache: Cache) => {
    const uuid = input.uuid ?? result.uuid;
    cache.updateQuery<ProductListQuery, ProductListQueryVariables>(
        {
            query: ProductListQueryDocument,
            variables: {
                input: { type: input.type, uuid },
            },
        },
        () => ({ __typename: 'Query', productList: result }),
    );
};
