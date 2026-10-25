import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useEffect, useRef, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { ProductVariantsPopupRowLayout, ProductVariantsPopupVariant } from './ProductVariantsPopupRow';

type ProductVariantsComparisonListProps = {
    variants: ProductVariantsPopupVariant[];
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
    onVariantCompared: () => void;
};

export const ProductVariantsComparisonList: FC<ProductVariantsComparisonListProps> = ({
    variants,
    gtmProductListName,
    listIndex,
    onVariantCompared,
}) => {
    const { t } = useTranslation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);
    const [pendingProductUuid, setPendingProductUuid] = useState<string | null>(null);
    const selectionPending = useRef(false);
    const isMounted = useRef(true);

    useEffect(() => {
        isMounted.current = true;
        return () => {
            isMounted.current = false;
        };
    }, []);

    const finishSelection = () => {
        onVariantCompared();
        if (isMounted.current) {
            closePortalContent();
        }
    };

    const { isProductInComparison, toggleProductInComparison } = useComparison({
        onProductAdded: () => {
            showSuccessMessage(t('Product added to comparison.'));
            finishSelection();
        },
        onAddProductError: () => {
            selectionPending.current = false;
            if (isMounted.current) {
                setPendingProductUuid(null);
            }
        },
    });

    const selectVariant = (variant: ProductVariantsPopupVariant) => {
        if (selectionPending.current) {
            return;
        }
        selectionPending.current = true;

        if (isProductInComparison(variant.uuid)) {
            finishSelection();
            return;
        }

        setPendingProductUuid(variant.uuid);
        if (toggleProductInComparison(variant, gtmProductListName, listIndex) === false) {
            selectionPending.current = false;
            setPendingProductUuid(null);
        }
    };

    return (
        <ul className="flex flex-col gap-3" data-tid={TIDs.product_variants_popup_list}>
            {variants.map((variant) => (
                <ProductVariantsPopupRowLayout
                    key={variant.uuid}
                    variant={variant}
                    action={
                        <Button
                            aria-busy={pendingProductUuid === variant.uuid}
                            aria-label={t('Add to comparison product {{ productName }}', {
                                ns: 'accessibility',
                                productName: variant.fullName,
                            })}
                            className="w-full"
                            disabled={pendingProductUuid !== null}
                            onClick={() => selectVariant(variant)}
                        >
                            {pendingProductUuid === variant.uuid && <Loader className="size-4" />}
                            {isProductInComparison(variant.uuid) ? t('In comparison') : t('Compare')}
                        </Button>
                    }
                />
            ))}
        </ul>
    );
};
