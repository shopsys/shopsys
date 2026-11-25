import { Alert } from 'components/Basic/Alert/Alert';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { ProductListItemButtons } from 'components/Blocks/Product/ProductsList/ProductListItemActions';
import { TIDs } from 'cypress/tids';
import { useProductVariantsQuery } from 'graphql/requests/products/queries/ProductVariantsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useEffect, useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { ProductVariantsPopupRow, ProductVariantsPopupVariant } from './ProductVariantsPopupRow';
import { ProductVariantsPopupSkeleton } from './ProductVariantsPopupSkeleton';

export type ProductVariantsPopupContentProps = {
    productUuid: string;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
    // The variant to return to after choosing another variant in the add to cart popup
    focusedVariantUuid?: string;
    onVariantAddedToComparison?: () => void;
    onChooseAnotherVariant: (addedVariantUuid: string) => void;
};

export const ProductVariantsPopupContent: FC<ProductVariantsPopupContentProps> = ({
    productUuid,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
    focusedVariantUuid,
    onVariantAddedToComparison,
    onChooseAnotherVariant,
}) => {
    const { t } = useTranslation();
    const variantListRef = useRef<HTMLUListElement>(null);
    const [{ data: productVariantsData, fetching: isFetching, error }] = useProductVariantsQuery({
        variables: { uuid: productUuid },
        // Refresh availability and customer prices each time the selector opens.
        requestPolicy: 'network-only',
    });
    const product = productVariantsData?.product;
    const hasLoadedVariants = !isFetching && product?.__typename === 'MainVariant';

    const { isProductInComparison, toggleProductInComparison } = useComparison();
    const { isProductInWishlist, toggleProductInWishlist } = useWishlist();

    useEffect(() => {
        if (!focusedVariantUuid || !hasLoadedVariants) {
            return;
        }

        const focusedVariantPurchaseAction = variantListRef.current?.querySelector(
            `[data-variant-uuid="${focusedVariantUuid}"] [data-variant-purchase-action]`,
        );
        focusedVariantPurchaseAction?.querySelector<HTMLElement>('button, input, a[href]')?.focus();
    }, [focusedVariantUuid, hasLoadedVariants]);

    const toggleVariantInComparison = async (variant: ProductVariantsPopupVariant) => {
        const isAddingVariant = !isProductInComparison(variant.uuid);
        const comparison = await toggleProductInComparison(variant, gtmProductListName, listIndex);

        if (isAddingVariant && comparison) {
            onVariantAddedToComparison?.();
        }
    };

    if (isFetching) {
        return <ProductVariantsPopupSkeleton />;
    }

    if (error || product?.__typename !== 'MainVariant') {
        return (
            <div role="alert">
                <Alert icon={WarningIcon} variant="error">
                    {t('Product variants could not be loaded. Please try again later.')}
                </Alert>
            </div>
        );
    }

    if (product.variants.length === 0) {
        return (
            <Alert icon={InfoIcon} variant="info">
                {t('No variants are available.')}
            </Alert>
        );
    }

    return (
        <ul className="flex flex-col gap-3" data-tid={TIDs.product_variants_popup_list} ref={variantListRef}>
            {product.variants.map((variant) => (
                <ProductVariantsPopupRow
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    key={variant.uuid}
                    listIndex={listIndex}
                    variant={variant}
                    onChooseAnotherVariant={() => onChooseAnotherVariant(variant.uuid)}
                    listActions={
                        <ProductListItemButtons
                            allowKeyboardFocus
                            isProductInComparison={isProductInComparison(variant.uuid)}
                            isProductInWishlist={isProductInWishlist(variant.uuid)}
                            productName={variant.fullName}
                            toggleProductInComparison={() => toggleVariantInComparison(variant)}
                            toggleProductInWishlist={() =>
                                toggleProductInWishlist(variant, gtmProductListName, listIndex)
                            }
                        />
                    }
                />
            ))}
        </ul>
    );
};
