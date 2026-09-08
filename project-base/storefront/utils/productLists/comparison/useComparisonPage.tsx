import {
    COMPARISON_UNDO_TOAST_ID,
    ProductComparisonUndoAction,
} from 'components/Pages/ProductComparison/ProductComparisonUndoAction';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { TypeProductListFragment } from 'graphql/requests/productLists/fragments/ProductListFragment.generated';
import { useMoveProductInListMutation } from 'graphql/requests/productLists/mutations/MoveProductInListMutation.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageReadyEvents/productList/useGtmSliderProductListViewEvent';
import dynamic from 'next/dynamic';
import { useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'react-toastify';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { useLatest } from 'utils/ui/useLatest';

const RemoveAllProductsPopup = dynamic(
    () =>
        import('components/Blocks/Popup/RemoveAllProductsPopup').then((component) => component.RemoveAllProductsPopup),
    { ssr: false },
);

export const useComparisonPage = () => {
    const { t } = useTranslation();
    const [, moveProductInListMutation] = useMoveProductInListMutation();
    const emptyStateRef = useRef<HTMLDivElement>(null);
    const focusEmptyStateAfterRemoval = useRef(false);
    const [restoringProduct, setRestoringProduct] = useState<{ uuid: string; index: number } | null>(null);
    const undoRequests = useRef(new Map<string, (list: TypeProductListFragment | null | undefined) => void>());
    const { comparison, isProductListFetching, removeComparison, toggleProductInComparison, isProductInComparison } =
        useComparison({
            onProductRemoved: handleProductRemoved,
            onProductAdded: (uuid, list) => finishUndo(uuid, list),
            onAddProductError: (uuid) => finishUndo(uuid, null),
        });
    const latestComparisonActions = useLatest({ toggleProductInComparison, isProductInComparison, comparison });

    useEffect(
        () => () => {
            toast.dismiss(COMPARISON_UNDO_TOAST_ID);
        },
        [],
    );

    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const products = useMemo(() => {
        const items = comparison?.products ?? [];
        const restoredProduct = restoringProduct && items.find((item) => item.uuid === restoringProduct.uuid);
        if (!restoredProduct || !restoringProduct) return items;

        // Adding and reordering are separate requests; hide the intermediate server order.
        const ordered = items.filter((item) => item.uuid !== restoredProduct.uuid);
        ordered.splice(Math.min(restoringProduct.index, ordered.length), 0, restoredProduct);
        return ordered;
    }, [comparison?.products, restoringProduct]);

    useEffect(() => {
        if (!products.length && focusEmptyStateAfterRemoval.current) {
            focusEmptyStateAfterRemoval.current = false;
            if (document.activeElement === document.body) {
                emptyStateRef.current?.focus();
            }
        }
    }, [products.length]);

    useGtmSliderProductListViewEvent(comparison?.products, GtmProductListNameType.product_comparison_page);

    function finishUndo(uuid: string, list: TypeProductListFragment | null | undefined): void {
        undoRequests.current.get(uuid)?.(list);
        undoRequests.current.delete(uuid);
    }

    async function undoRemoval(product: TypeProductInProductListFragment, index: number): Promise<boolean> {
        setRestoringProduct({ uuid: product.uuid, index });
        try {
            const actions = latestComparisonActions.current;
            const list = actions.isProductInComparison(product.uuid)
                ? actions.comparison
                : await new Promise<TypeProductListFragment | null | undefined>((resolve) => {
                      undoRequests.current.set(product.uuid, resolve);
                      actions.toggleProductInComparison(product, GtmProductListNameType.product_comparison_page);
                  });
            if (!list) return false;
            // The added product lands on top; move it back after the product that preceded it
            const others = list.products.filter((item) => item.uuid !== product.uuid);
            const afterProduct = others[Math.min(index, others.length) - 1];
            return afterProduct ? await moveProduct(product.uuid, afterProduct.uuid, list.uuid) : true;
        } finally {
            setRestoringProduct(null);
        }
    }

    function handleProductRemoved(productUuid: string): void {
        const index = products.findIndex((item) => item.uuid === productUuid);
        const product = products[index];
        if (product) {
            showSuccessMessage(t('{{ productName }} was removed from comparison.', { productName: product.fullName }), {
                toastId: COMPARISON_UNDO_TOAST_ID,
                autoClose: 10000,
                updateExisting: true,
                action: <ProductComparisonUndoAction key={product.uuid} onUndo={() => undoRemoval(product, index)} />,
            });
        }
    }

    const handleRemove = (product: TypeProductInProductListFragment) => {
        focusEmptyStateAfterRemoval.current =
            products.length === 1 && !!document.activeElement?.closest('[data-comparison-product]');
        toggleProductInComparison(
            product,
            GtmProductListNameType.product_comparison_page,
            products.findIndex((item) => item.uuid === product.uuid),
        );
    };

    const handleRemoveAll = () => {
        updatePortalContent(
            <RemoveAllProductsPopup
                description={t('All products in comparison will be removed ({{ productCount }} in total).', {
                    productCount: products.length,
                })}
                removeAllHandler={removeComparison}
            />,
        );
    };

    const moveProduct = async (
        productUuid: string,
        afterProductUuid: string | null,
        listUuid = comparison?.uuid,
    ): Promise<boolean> => {
        if (!listUuid) return false;
        try {
            const result = await moveProductInListMutation({
                input: {
                    productListInput: { uuid: listUuid, type: TypeProductListTypeEnum.Comparison },
                    productUuid,
                    afterProductUuid,
                },
            });
            if (!result.error && result.data?.MoveProductInList) return true;
        } catch {
            // Network failures must restore the last confirmed order as well.
        }
        showErrorMessage(t('Unable to save product order.'));
        return false;
    };

    return {
        moveProduct,
        products,
        isLoading: isProductListFetching && !comparison,
        emptyStateRef,
        handleRemove,
        handleRemoveAll,
    };
};
