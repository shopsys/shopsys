import { useUpdateProductListUuid } from './useUpdateProductListUuid';
import {
    ProductListFragmentApi,
    ProductListTypeEnumApi,
    useAddProductToListMutationApi,
    useCleanProductListMutationApi,
    useProductListQueryApi,
    useRemoveProductFromListMutationApi,
} from 'graphql/generated';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useEffect } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useProductList = (
    productListType: ProductListTypeEnumApi,
    callbacks: {
        cleanSuccess: () => void;
        cleanError: () => void;
        addSuccess: (result: ProductListFragmentApi | null | undefined) => void;
        addError: () => void;
        removeSuccess: (result: ProductListFragmentApi | null | undefined) => void;
        removeError: () => void;
    },
) => {
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const updateProductListUuid = useUpdateProductListUuid(productListType);
    const productListUuid = productListUuids[productListType] ?? null;
    const isUserLoggedIn = useIsUserLoggedIn();

    const [, addProductToListMutation] = useAddProductToListMutationApi();
    const [, removeProductFromListMutation] = useRemoveProductFromListMutationApi();
    const [, cleanListMutation] = useCleanProductListMutationApi();

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

    const cleanList = async () => {
        const cleanListResult = await cleanListMutation({
            input: {
                type: productListType,
                uuid: productListUuid,
            },
        });

        if (cleanListResult.error) {
            callbacks.cleanError();
        } else {
            callbacks.cleanSuccess();
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
            callbacks.addError();
        } else {
            callbacks.addSuccess(addProductToListResult.data?.AddProductToList);
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
            callbacks.removeError();
        } else {
            callbacks.removeSuccess(removeProductFromListResult.data?.RemoveProductFromList);
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

    return { productListData, isProductInList, cleanList, toggleProductInList, fetching };
};
