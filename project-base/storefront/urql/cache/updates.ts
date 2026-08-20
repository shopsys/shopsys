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
    CartQueryDocument,
    TypeCartQuery,
    TypeCartQueryVariables,
} from 'graphql/requests/cart/queries/CartQuery.generated';
import {
    TypeCreateDeliveryAddressMutation,
    TypeCreateDeliveryAddressMutationVariables,
} from 'graphql/requests/customer/mutations/CreateDeliveryAddressMutation.generated';
import {
    TypeDeleteDeliveryAddressMutation,
    TypeDeleteDeliveryAddressMutationVariables,
} from 'graphql/requests/customer/mutations/DeleteDeliveryAddressMutation.generated';
import {
    CurrentCustomerUserQueryDocument,
    TypeCurrentCustomerUserQuery,
    TypeCurrentCustomerUserQueryVariables,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
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
    ProductListCountQueryDocument,
    TypeProductListCountQuery,
    TypeProductListCountQueryVariables,
} from 'graphql/requests/productLists/queries/ProductListCountQuery.generated';
import {
    ProductListQueryDocument,
    TypeProductListQuery,
    TypeProductListQueryVariables,
} from 'graphql/requests/productLists/queries/ProductListQuery.generated';
import {
    TypeRegistrationByOrderMutation,
    TypeRegistrationByOrderMutationVariables,
} from 'graphql/requests/registration/mutations/RegistrationByOrderMutation.generated';
import {
    TypeRegistrationMutation,
    TypeRegistrationMutationVariables,
} from 'graphql/requests/registration/mutations/RegistrationMutation.generated';
import { Maybe, TypeProductListInput } from 'graphql/types';
import { invalidateFields } from './cacheUtils';

type MakeMaybe<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]: Maybe<T[SubKey]> };

export const cacheUpdates: UpdatesConfig = {
    Mutation: {
        Login(_result: TypeLoginMutation, _args: TypeLoginMutationVariables, cache) {
            invalidateAuthenticationDependentFields(cache);
        },
        Logout(_result: TypeLogoutMutation, _args: TypeLogoutMutationVariables, cache) {
            invalidateAuthenticationDependentFields(cache);
        },
        Register(_result: TypeRegistrationMutation, _args: TypeRegistrationMutationVariables, cache) {
            invalidateAuthenticationDependentFields(cache);
        },
        RegisterByOrder(
            _result: TypeRegistrationByOrderMutation,
            _args: TypeRegistrationByOrderMutationVariables,
            cache,
        ) {
            invalidateAuthenticationDependentFields(cache);
        },
        DeleteDeliveryAddress(
            result: TypeDeleteDeliveryAddressMutation,
            _args: TypeDeleteDeliveryAddressMutationVariables,
            cache,
        ) {
            manuallyUpdateCurrentCustomerUserDeliveryAddresses(cache, result.DeleteDeliveryAddress);
        },
        CreateOrder(_result: TypeCreateOrderMutation, _args: TypeCreateOrderMutationVariables, cache) {
            invalidateFields(cache, ['currentCustomerUser']);
        },
        CreateDeliveryAddress(
            result: TypeCreateDeliveryAddressMutation,
            _args: TypeCreateDeliveryAddressMutationVariables,
            cache,
        ) {
            manuallyUpdateCurrentCustomerUserDeliveryAddresses(cache, result.CreateDeliveryAddress);
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
        OrderWithdrawalRequest(_result, _args, cache) {
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

const invalidateAuthenticationDependentFields = (cache: Cache) => {
    invalidateFields(cache, ['cart', 'currentCustomerUser']);
    cache.invalidate('ProductPrice');
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

const manuallyUpdateCurrentCustomerUserDeliveryAddresses = (
    cache: Cache,
    deliveryAddresses: NonNullable<TypeCurrentCustomerUserQuery['currentCustomerUser']>['deliveryAddresses'],
) => {
    cache.updateQuery<TypeCurrentCustomerUserQuery, TypeCurrentCustomerUserQueryVariables>(
        { query: CurrentCustomerUserQueryDocument },
        (data) => {
            if (data?.currentCustomerUser === null || data?.currentCustomerUser === undefined) {
                return data;
            }

            const defaultDeliveryAddress =
                deliveryAddresses.find((deliveryAddress) => {
                    return deliveryAddress.uuid === data.currentCustomerUser?.defaultDeliveryAddress?.uuid;
                }) ?? null;

            return {
                ...data,
                currentCustomerUser: {
                    ...data.currentCustomerUser,
                    defaultDeliveryAddress,
                    deliveryAddresses,
                },
            };
        },
    );
};

const manuallyRemoveProductListQuery = (cache: Cache, args: TypeProductListInput) => {
    const nullData = { __typename: 'Query' as const, productList: null };

    cache.updateQuery<TypeProductListQuery, TypeProductListQueryVariables>(
        { query: ProductListQueryDocument, variables: { input: args } },
        () => nullData,
    );
    cache.updateQuery<TypeProductListCountQuery, TypeProductListCountQueryVariables>(
        { query: ProductListCountQueryDocument, variables: { input: args } },
        () => nullData,
    );

    // Also clear the cache entry for uuid: null, which may have been written
    // by the dual-write in manuallyUpdateProductListQuery when the list was first created
    if (args.uuid) {
        const nullUuidInput = { type: args.type, uuid: null };
        cache.updateQuery<TypeProductListQuery, TypeProductListQueryVariables>(
            { query: ProductListQueryDocument, variables: { input: nullUuidInput } },
            () => nullData,
        );
        cache.updateQuery<TypeProductListCountQuery, TypeProductListCountQueryVariables>(
            { query: ProductListCountQueryDocument, variables: { input: nullUuidInput } },
            () => nullData,
        );
    }
};

const manuallyUpdateProductListQuery = (input: TypeProductListInput, result: TypeProductListFragment, cache: Cache) => {
    const uuid = input.uuid ?? result.uuid;
    const queryInput = { type: input.type, uuid };
    const countData = {
        __typename: 'Query' as const,
        productList: { __typename: 'ProductList' as const, uuid, itemsCount: result.products.length },
    };

    cache.updateQuery<TypeProductListQuery, TypeProductListQueryVariables>(
        { query: ProductListQueryDocument, variables: { input: queryInput } },
        () => ({ __typename: 'Query', productList: result }),
    );
    cache.updateQuery<TypeProductListCountQuery, TypeProductListCountQueryVariables>(
        { query: ProductListCountQueryDocument, variables: { input: queryInput } },
        () => countData,
    );

    // When the mutation was sent with a different UUID than what the result contains
    // (e.g. null UUID when creating a new list), also update the cache entry
    // that the count query hook is currently subscribed to
    if (input.uuid !== uuid) {
        const originalInput = { type: input.type, uuid: input.uuid ?? null };
        cache.updateQuery<TypeProductListCountQuery, TypeProductListCountQueryVariables>(
            { query: ProductListCountQueryDocument, variables: { input: originalInput } },
            () => countData,
        );
        cache.updateQuery<TypeProductListQuery, TypeProductListQueryVariables>(
            { query: ProductListQueryDocument, variables: { input: originalInput } },
            () => ({ __typename: 'Query', productList: result }),
        );
    }
};
