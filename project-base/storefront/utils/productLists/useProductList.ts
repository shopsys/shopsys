import { useUpdateProductListUuid } from './useUpdateProductListUuid';
import { TypeProductListFragment } from 'graphql/requests/productLists/fragments/ProductListFragment.generated';
import { useAddProductToListMutation } from 'graphql/requests/productLists/mutations/AddProductToListMutation.generated';
import { useRemoveProductFromListMutation } from 'graphql/requests/productLists/mutations/RemoveProductFromListMutation.generated';
import { useRemoveProductListMutation } from 'graphql/requests/productLists/mutations/RemoveProductListMutation.generated';
import { useProductListQuery } from 'graphql/requests/productLists/queries/ProductListQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';

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
        pause: (!productListUuid && !isUserLoggedIn) || authLoading !== null,
    });

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
            callbacks.removeError();
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
            callbacks.addProductError();
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
            callbacks.removeProductError();
        } else {
            callbacks.removeProductSuccess(removeProductFromListResult.data?.RemoveProductFromList);
        }
    };

    const isProductInList = (productUuid: string) =>
        !!productListData?.productList?.products.find((product) => product.uuid === productUuid);

    const toggleProductInList = (productUuid: string) => {
        if (isProductInList(productUuid)) {
            removeFromList(productUuid);
        } else {
            addToList(productUuid);
        }
    };

    return { productListData, isProductInList, removeList, toggleProductInList, isProductListFetching };
};
