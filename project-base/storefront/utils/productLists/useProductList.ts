import { TypeProductListFragment } from 'graphql/requests/productLists/fragments/ProductListFragment.generated';
import { useAddProductToListMutation } from 'graphql/requests/productLists/mutations/AddProductToListMutation.generated';
import { useRemoveProductFromListMutation } from 'graphql/requests/productLists/mutations/RemoveProductFromListMutation.generated';
import { useRemoveProductListMutation } from 'graphql/requests/productLists/mutations/RemoveProductListMutation.generated';
import {
    ProductListQueryDocument,
    TypeProductListQuery,
    TypeProductListQueryVariables,
    useProductListQuery,
} from 'graphql/requests/productLists/queries/ProductListQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useCallback, useEffect, useEffectEvent, useRef, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useClient } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useRefetchOnTabFocus } from 'utils/useRefetchOnTabFocus';
import { useUpdateProductListUuid } from './useUpdateProductListUuid';

export const useProductList = (
    productListType: TypeProductListTypeEnum,
    callbacks: {
        removeSuccess: () => void;
        removeError: () => void;
        addProductSuccess: (updatedProductList: TypeProductListFragment | null | undefined) => void;
        addProductError: () => void;
        removeProductSuccess: (updatedProductList: TypeProductListFragment | null | undefined) => void;
        removeProductError: () => void;
    },
) => {
    const client = useClient();
    const { t } = useTranslation();
    const isProductListHydrated = useSessionStore((s) => s.isProductListHydrated);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const authLoading = usePersistStore((s) => s.authLoading);
    const updateProductListUuid = useUpdateProductListUuid(productListType);
    const productListUuid = productListUuids[productListType] ?? null;
    const isUserLoggedIn = useIsUserLoggedIn();

    const [, TypeAddProductToListMutation] = useAddProductToListMutation();
    const [, TypeRemoveProductFromListMutation] = useRemoveProductFromListMutation();
    const [, removeListMutation] = useRemoveProductListMutation();

    const mutatingProductUuidsRef = useRef(new Set<string>());

    const isQueryPaused = !isProductListHydrated || (!productListUuid && !isUserLoggedIn) || authLoading !== null;

    const [{ data: productListData, fetching }, reexecuteQuery] = useProductListQuery({
        variables: {
            input: {
                type: productListType,
                uuid: productListUuid,
            },
        },
        pause: isQueryPaused,
    });

    const refetchOnFocus = useCallback(() => {
        if (isQueryPaused) {
            return;
        }
        reexecuteQuery({ requestPolicy: 'network-only' });
    }, [reexecuteQuery, isQueryPaused]);
    useRefetchOnTabFocus(refetchOnFocus);

    const [productListOverride, setProductListOverride] = useState<TypeProductListFragment | null>(null);

    useEffect(() => {
        setProductListOverride(null);
    }, [productListData]);

    const currentProductList = productListOverride ?? productListData?.productList ?? null;

    // Consider loading when query is paused due to hydration not being complete yet
    const isProductListFetching = fetching || !isProductListHydrated;

    const refetchWithNetworkOnly = async () => {
        const result = await client
            .query<TypeProductListQuery, TypeProductListQueryVariables>(
                ProductListQueryDocument,
                {
                    input: { type: productListType, uuid: productListUuid },
                },
                { requestPolicy: 'network-only' },
            )
            .toPromise();

        return result.data;
    };

    useEffect(() => {
        updatePageLoadingState({ isProductListHydrated: true });
    }, [updatePageLoadingState]);

    const onUpdateProductListUuid = useEffectEvent((uuid: string | null) => {
        updateProductListUuid(uuid);
    });

    useEffect(() => {
        if (isQueryPaused || productListData === undefined) {
            return;
        }

        onUpdateProductListUuid(productListData?.productList?.uuid ?? null);
        // Sync only when the UUID value changes, not on every productListData reference change.
    }, [productListData?.productList?.uuid, isQueryPaused]);

    const removeList = async () => {
        const removeListResult = await removeListMutation({
            input: {
                type: productListType,
                uuid: productListUuid,
            },
        });

        if (removeListResult.error) {
            const { applicationError } = getUserFriendlyErrors(removeListResult.error, t);

            if (applicationError?.type === `${productListType}-product-list-not-found`) {
                callbacks.removeSuccess();
            } else {
                callbacks.removeError();
            }
        } else {
            callbacks.removeSuccess();
        }
    };

    const addToList = async (productUuid: string) => {
        mutatingProductUuidsRef.current.add(productUuid);

        try {
            const addProductToListResult = await TypeAddProductToListMutation({
                input: {
                    productUuid,
                    productListInput: {
                        uuid: productListUuid,
                        type: productListType,
                    },
                },
            });

            if (addProductToListResult.error) {
                const { applicationError } = getUserFriendlyErrors(addProductToListResult.error, t);

                if (applicationError?.type === `${productListType}-product-already-in-list`) {
                    const freshProductListData = await refetchWithNetworkOnly();
                    callbacks.addProductSuccess(freshProductListData?.productList);
                } else {
                    callbacks.addProductError();
                }
            } else {
                if (addProductToListResult.data?.AddProductToList) {
                    setProductListOverride(addProductToListResult.data.AddProductToList);
                }
                callbacks.addProductSuccess(addProductToListResult.data?.AddProductToList);
            }
        } finally {
            mutatingProductUuidsRef.current.delete(productUuid);
        }
    };

    const removeFromList = async (productUuid: string) => {
        mutatingProductUuidsRef.current.add(productUuid);

        try {
            const removeProductFromListResult = await TypeRemoveProductFromListMutation({
                input: {
                    productUuid,
                    productListInput: {
                        uuid: productListUuid,
                        type: productListType,
                    },
                },
            });

            if (removeProductFromListResult.error) {
                const { applicationError } = getUserFriendlyErrors(removeProductFromListResult.error, t);

                if (
                    applicationError?.type === `${productListType}-product-not-in-list` ||
                    applicationError?.type === `${productListType}-product-list-not-found`
                ) {
                    const freshProductListData = await refetchWithNetworkOnly();
                    callbacks.removeProductSuccess(freshProductListData?.productList);
                } else {
                    callbacks.removeProductError();
                }
            } else {
                if (removeProductFromListResult.data?.RemoveProductFromList) {
                    setProductListOverride(removeProductFromListResult.data.RemoveProductFromList);
                }
                callbacks.removeProductSuccess(removeProductFromListResult.data?.RemoveProductFromList);
            }
        } finally {
            mutatingProductUuidsRef.current.delete(productUuid);
        }
    };

    const isProductInList = (productUuid: string) => {
        if (!productListUuid) {
            return false;
        }

        return !!currentProductList?.products.find((product) => product.uuid === productUuid);
    };

    const toggleProductInList = (productUuid: string) => {
        if (mutatingProductUuidsRef.current.has(productUuid)) {
            return;
        }

        // Block all mutations when no list exists yet — concurrent creates would produce duplicate lists
        if (!productListUuid && mutatingProductUuidsRef.current.size > 0) {
            return;
        }

        if (isProductInList(productUuid)) {
            removeFromList(productUuid);
        } else {
            addToList(productUuid);
        }
    };

    return {
        productListData: !productListUuid ? undefined : productListData,
        isProductInList,
        removeList,
        toggleProductInList,
        isProductListFetching,
    };
};
