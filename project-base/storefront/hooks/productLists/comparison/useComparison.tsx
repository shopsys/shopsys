import { ProductListTypeEnumApi } from 'graphql/generated';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useProductList } from 'hooks/productLists/useProductList';
import { useUpdateProductListUuid } from 'hooks/productLists/useUpdateProductListUuid';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';

const ProductComparePopup = dynamic(() =>
    import('components/Blocks/Product/ButtonsAction/ProductComparePopup').then(
        (component) => component.ProductComparePopup,
    ),
);

export const useComparison = () => {
    const { t } = useTranslation();
    const updateComparisonUuid = useUpdateProductListUuid(ProductListTypeEnumApi.ComparisonApi);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const { productListData, removeList, isProductInList, toggleProductInList, fetching } = useProductList(
        ProductListTypeEnumApi.ComparisonApi,

        {
            addProductError: () => showErrorMessage(t('Unable to add product to comparison.')),
            addProductSuccess: (result) => {
                updatePortalContent(<ProductComparePopup />);
                updateComparisonUuid(result?.uuid ?? null);
            },
            removeError: () => showErrorMessage(t('Unable to clean product comparison.')),
            removeSuccess: () => {
                showSuccessMessage(t('Comparison products have been cleaned.'));
                updateComparisonUuid(null);
            },
            removeProductError: () => showErrorMessage(t('Unable to remove product from comparison.')),
            removeProductSuccess: (result) => {
                if (!result) {
                    updateComparisonUuid(null);
                }

                showSuccessMessage(t('Product has been removed from your comparison.'));
            },
        },
    );

    return {
        comparison: productListData?.productList,
        fetching,
        isProductInComparison: isProductInList,
        toggleProductInComparison: toggleProductInList,
        removeComparison: removeList,
    };
};
