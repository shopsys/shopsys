import { useProductListState, useProductLists } from 'components/providers/ProductListsProvider';
import { useAddProductToListMutation } from 'graphql/requests/productLists/mutations/AddProductToListMutation.generated';
import { useMoveProductInListMutation } from 'graphql/requests/productLists/mutations/MoveProductInListMutation.generated';
import { useRemoveProductFromListMutation } from 'graphql/requests/productLists/mutations/RemoveProductFromListMutation.generated';
import { useRemoveProductListMutation } from 'graphql/requests/productLists/mutations/RemoveProductListMutation.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const useProductList = (
    productListType: TypeProductListTypeEnum,
    callbacks: {
        removeSuccess: () => void;
        removeError: () => void;
        addProductSuccess: (productUuid: string) => void;
        addProductError: (productUuid: string) => void;
        removeProductSuccess: (productUuid: string) => void;
        removeProductError: (productUuid: string) => void;
    },
) => {
    const state = useProductListState(productListType);
    const { notifyChange } = useProductLists();
    const { t } = useTranslation();
    const [, addMutation] = useAddProductToListMutation();
    const [, removeMutation] = useRemoveProductFromListMutation();
    const [, clearMutation] = useRemoveProductListMutation();
    const [, moveMutation] = useMoveProductInListMutation();

    const isProductInList = (uuid: string) => !!state.summary?.productUuids.includes(uuid);

    const clearList = async () => {
        const selection = state.getSelection();
        try {
            const result = await clearMutation({ input: { type: productListType, uuid: selection.uuid } });
            if (!state.isCurrentSelection(selection)) return;
            if (result.error) {
                const { applicationError } = getUserFriendlyErrors(result.error, t);
                if (applicationError?.type !== `${productListType}-product-list-not-found`) {
                    throw result.error;
                }
                state.invalidate();
            }
            state.applyMutationResult(null, selection);
            notifyChange(productListType, selection.uuid);
            callbacks.removeSuccess();
        } catch {
            if (state.isCurrentSelection(selection)) callbacks.removeError();
        }
    };
    const removeList = async () => {
        await state.enqueueMutation('clear', clearList);
    };

    const changeProduct = async (productUuid: string, remove: boolean) => {
        const selection = state.getSelection();
        const onError = remove ? callbacks.removeProductError : callbacks.addProductError;
        const onSuccess = remove ? callbacks.removeProductSuccess : callbacks.addProductSuccess;
        try {
            const input = { productUuid, productListInput: { uuid: selection.uuid, type: productListType } };
            const result = remove ? await removeMutation({ input }) : await addMutation({ input });
            if (!state.isCurrentSelection(selection)) return;
            const data = result.data;
            let list = data && ('RemoveProductFromList' in data ? data.RemoveProductFromList : data.AddProductToList);
            if (result.error) {
                const { applicationError } = getUserFriendlyErrors(result.error, t);
                const recoverable = remove
                    ? [`${productListType}-product-not-in-list`, `${productListType}-product-list-not-found`]
                    : [`${productListType}-product-already-in-list`];
                if (!applicationError || !recoverable.includes(applicationError.type)) {
                    throw result.error;
                }
                // Recovery waits for visibility and must not turn an unavailable response into a deleted list.
                list = await state.refreshForRecovery();
            } else {
                if (list === undefined) throw new Error('Product list response is missing.');
                state.applyMutationResult(list, selection);
            }
            notifyChange(productListType, selection.uuid ?? list?.uuid ?? null);
            onSuccess(productUuid);
            return list;
        } catch {
            if (state.isCurrentSelection(selection)) onError(productUuid);
        }
    };

    const toggleProductInList = (productUuid: string) => {
        const remove = isProductInList(productUuid);
        return state.enqueueMutation(productUuid, () => changeProduct(productUuid, remove));
    };

    const moveConfirmedProduct = async (
        productUuid: string,
        afterProductUuid: string | null,
        uuid = state.getSelection().uuid,
    ) => {
        const selection = state.getSelection();
        if (!uuid || uuid !== selection.uuid) return false;
        try {
            const result = await moveMutation({
                input: { productListInput: { uuid, type: productListType }, productUuid, afterProductUuid },
            });
            if (result.error || !result.data?.MoveProductInList || !state.isCurrentSelection(selection)) return false;
            state.applyMutationResult(result.data.MoveProductInList, selection);
            notifyChange(productListType, selection.uuid);
            return true;
        } catch {
            return false;
        }
    };

    const moveProduct = async (productUuid: string, afterProductUuid: string | null, uuid?: string | null) =>
        (await state.enqueueMutation(Symbol('move'), () =>
            moveConfirmedProduct(productUuid, afterProductUuid, uuid),
        )) ?? false;

    return {
        productListSummary: state.summary,
        isProductInList,
        removeList,
        toggleProductInList,
        moveProduct,
        isProductListFetching: state.fetching,
        hasProductListError: state.error,
    };
};
