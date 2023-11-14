import {
    CleanProductListMutationApi,
    CleanProductListMutationVariablesApi,
    ProductListTypeEnumApi,
    ProductListUpdateInputApi,
} from 'graphql/generated';
import { UseMutationExecute } from 'urql';

type GenericProductList<T> = { uuid: string; products: ({ uuid: string } & T)[] } | null;

export const useProductList = <T>(
    productListType: ProductListTypeEnumApi,
    productListUuid: string | null,
    productListData: { productList: GenericProductList<T> } | undefined,
    mutations: {
        cleanList: UseMutationExecute<CleanProductListMutationApi, CleanProductListMutationVariablesApi>;
        addProductToList: UseMutationExecute<
            { AddProductToList: GenericProductList<T> },
            { input: ProductListUpdateInputApi }
        >;
        removeProductFromList: UseMutationExecute<
            { RemoveProductFromList: GenericProductList<T> },
            { input: ProductListUpdateInputApi }
        >;
    },
    callbacks: {
        cleanSuccess: () => void;
        cleanError: () => void;
        addSuccess: (result: GenericProductList<T> | undefined) => void;
        addError: () => void;
        removeSuccess: (result: GenericProductList<T> | undefined) => void;
        removeError: () => void;
    },
) => {
    const cleanList = async () => {
        const cleanListResult = await mutations.cleanList({
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
        const addProductToListResult = await mutations.addProductToList({
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
        const removeProductFromListResult = await mutations.removeProductFromList({
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

    return {
        isProductInList,
        cleanList,
        toggleProductInList,
    };
};
