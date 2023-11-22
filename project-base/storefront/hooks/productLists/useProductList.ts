import { useUpdateProductListUuid } from './useUpdateProductListUuid';
import {
    ProductListFragmentApi,
    ProductListTypeEnumApi,
    useAddProductToListMutationApi,
    useRemoveProductListMutationApi,
    useProductListQueryApi,
    useRemoveProductFromListMutationApi,
} from 'graphql/generated';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useProductList = (
    productListType: ProductListTypeEnumApi,
    callbacks: {
        removeSuccess: () => void;
        removeError: () => void;
        addProductSuccess: (result: ProductListFragmentApi | null | undefined) => void;
        addProductError: () => void;
        removeProductSuccess: (result: ProductListFragmentApi | null | undefined) => void;
        removeProductError: () => void;
    },
) => {
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const updateProductListUuid = useUpdateProductListUuid(productListType);
    const productListUuid = productListUuids[productListType] ?? null;
    const isUserLoggedIn = useIsUserLoggedIn();

    const [, addProductToListMutation] = useAddProductToListMutationApi();
    const [, removeProductFromListMutation] = useRemoveProductFromListMutationApi();
    const [, removeListMutation] = useRemoveProductListMutationApi();

    const [{ data: productListData, fetching }] = useProductListQueryApi({
        variables: {
            input: {
                type: productListType,
                uuid: productListUuid,
            },
        },
        pause: !productListUuid && !isUserLoggedIn,
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
        const addProductToListResult = await addProductToListMutation({
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
        const removeProductFromListResult = await removeProductFromListMutation({
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

    return { productListData, isProductInList, removeList, toggleProductInList, fetching };
};
