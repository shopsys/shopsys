import { useUpdateProductListUuid } from './useUpdateProductListUuid';
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
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useClient } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';

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

    const [{ data: productListData, fetching: isProductListFetching }] = useProductListQuery({
        variables: {
            input: {
                type: productListType,
                uuid: productListUuid,
            },
        },
        pause: !isProductListHydrated || (!productListUuid && !isUserLoggedIn) || authLoading !== null,
    });

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
    }, []);

    useEffect(() => {
        if (productListData?.productList?.uuid) {
            updateProductListUuid(productListData.productList.uuid);
        }
    }, [productListData?.productList?.uuid]);

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
            callbacks.addProductSuccess(addProductToListResult.data?.AddProductToList);
        }
    };

    const removeFromList = async (productUuid: string) => {
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
            callbacks.removeProductSuccess(removeProductFromListResult.data?.RemoveProductFromList);
        }
    };

    const isProductInList = (productUuid: string) => {
        if (!productListUuid) {
            return false;
        }

        return !!productListData?.productList?.products.find((product) => product.uuid === productUuid);
    };

    const toggleProductInList = (productUuid: string) => {
        if (isProductInList(productUuid)) {
            removeFromList(productUuid);
        } else {
            addToList(productUuid);
        }
    };

    return {
        productListData: productListUuid ? productListData : null,
        isProductInList,
        removeList,
        toggleProductInList,
        isProductListFetching,
    };
};
