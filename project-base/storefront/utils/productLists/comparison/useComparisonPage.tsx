import {
    COMPARISON_UNDO_TOAST_ID,
    ProductComparisonUndoAction,
} from 'components/Pages/ProductComparison/ProductComparisonUndoAction';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useReorderProductListMutation } from 'graphql/requests/productLists/mutations/ReorderProductListMutation.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmSliderProductListViewEvent } from 'gtm/utils/pageReadyEvents/productList/useGtmSliderProductListViewEvent';
import dynamic from 'next/dynamic';
import { useEffect, useRef } from 'react';
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
    const [, reorderProductListMutation] = useReorderProductListMutation();
    const emptyStateRef = useRef<HTMLDivElement>(null);
    const focusEmptyStateAfterRemoval = useRef(false);
    const undoRequests = useRef(new Map<string, (success: boolean) => void>());
    const { comparison, isProductListFetching, removeComparison, toggleProductInComparison, isProductInComparison } =
        useComparison({
            onProductRemoved: handleProductRemoved,
            onProductAdded: (uuid) => finishUndo(uuid, true),
            onAddProductError: (uuid) => finishUndo(uuid, false),
        });
    const latestComparisonActions = useLatest({ toggleProductInComparison, isProductInComparison });

    useEffect(
        () => () => {
            toast.dismiss(COMPARISON_UNDO_TOAST_ID);
        },
        [],
    );

    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const products = comparison?.products ?? [];

    useEffect(() => {
        if (!products.length && focusEmptyStateAfterRemoval.current) {
            focusEmptyStateAfterRemoval.current = false;
            if (document.activeElement === document.body) {
                emptyStateRef.current?.focus();
            }
        }
    }, [products.length]);

    useGtmSliderProductListViewEvent(comparison?.products, GtmProductListNameType.product_comparison_page);

    function finishUndo(uuid: string, success: boolean): void {
        undoRequests.current.get(uuid)?.(success);
        undoRequests.current.delete(uuid);
    }

    function undoRemoval(product: TypeProductInProductListFragment): Promise<boolean> {
        const actions = latestComparisonActions.current;
        if (actions.isProductInComparison(product.uuid)) {
            return Promise.resolve(true);
        }
        return new Promise((resolve) => {
            undoRequests.current.set(product.uuid, resolve);
            actions.toggleProductInComparison(product, GtmProductListNameType.product_comparison_page);
        });
    }

    function handleProductRemoved(productUuid: string): void {
        const product = products.find((item) => item.uuid === productUuid);
        if (product) {
            showSuccessMessage(t('{{ productName }} was removed from comparison.', { productName: product.fullName }), {
                toastId: COMPARISON_UNDO_TOAST_ID,
                autoClose: 10000,
                updateExisting: true,
                action: <ProductComparisonUndoAction key={product.uuid} onUndo={() => undoRemoval(product)} />,
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

    const saveOrder = async (productUuids: string[]): Promise<boolean> => {
        if (!comparison) return false;
        try {
            const result = await reorderProductListMutation({
                input: {
                    productListInput: { uuid: comparison.uuid, type: TypeProductListTypeEnum.Comparison },
                    productUuids,
                },
            });
            if (!result.error && result.data?.ReorderProductList) return true;
        } catch {
            // Network failures must restore the last confirmed order as well.
        }
        showErrorMessage(t('Unable to save product order.'));
        return false;
    };

    return {
        saveOrder,
        products,
        isLoading: isProductListFetching && !comparison,
        emptyStateRef,
        handleRemove,
        handleRemoveAll,
    };
};
