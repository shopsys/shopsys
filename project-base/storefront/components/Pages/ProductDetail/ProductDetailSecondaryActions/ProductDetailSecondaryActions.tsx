import { useOpenProductVariantsPopup } from 'components/Blocks/Popup/useOpenProductVariantsPopup';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductQuestionButton } from 'components/Blocks/Product/ButtonsAction/ProductQuestionButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { scrollToProductVariants } from 'components/Pages/ProductDetail/scrollToProductVariants';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

export type ProductDetailSecondaryActionsProps = {
    product: TypeProductDetailFragment | TypeMainVariantDetailFragment;
};

export const ProductDetailSecondaryActions: FC<ProductDetailSecondaryActionsProps> = ({ product }) => {
    const { isProductInComparison, isProductOrItsVariantInComparison, toggleProductInComparison } = useComparison();
    const { isProductInWishlist, isProductOrItsVariantInWishlist, toggleProductInWishlist } = useWishlist();
    const openProductVariantsPopup = useOpenProductVariantsPopup();

    const isMainVariant = product.__typename === 'MainVariant';
    // A main variant is compared and saved through its variants listed below; a family stored earlier stays removable.
    const isComparisonVariantSelection = isMainVariant && !isProductInComparison(product.uuid);
    const isWishlistVariantSelection = isMainVariant && !isProductInWishlist(product.uuid);

    const iconButtonClassNames = 'min-w-0 flex-col items-center gap-1 text-center sm:shrink-0 sm:flex-row sm:text-left';

    // The popup explains the state when the detail has no variant list to scroll to.
    const showVariants = () => {
        if (!scrollToProductVariants()) {
            openProductVariantsPopup(product, { gtmProductListName: GtmProductListNameType.product_detail });
        }
    };

    const handleComparisonClick = () => {
        if (isComparisonVariantSelection) {
            showVariants();

            return;
        }

        toggleProductInComparison(product, GtmProductListNameType.product_detail);
    };

    const handleWishlistClick = () => {
        if (isWishlistVariantSelection) {
            showVariants();

            return;
        }

        toggleProductInWishlist(product, GtmProductListNameType.product_detail);
    };

    return (
        <div className="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap sm:items-center sm:gap-x-5 sm:gap-y-3">
            <ProductCompareButton
                isWithText
                isWithShortText
                className={iconButtonClassNames}
                isProductInComparison={isProductOrItsVariantInComparison(product.uuid)}
                isVariantSelection={isComparisonVariantSelection}
                productName={product.fullName}
                toggleProductInComparison={handleComparisonClick}
            />
            <ProductWishlistButton
                isWithText
                isWithShortText
                className={iconButtonClassNames}
                isProductInWishlist={isProductOrItsVariantInWishlist(product.uuid)}
                isVariantSelection={isWishlistVariantSelection}
                productName={product.fullName}
                toggleProductInWishlist={handleWishlistClick}
            />
            <ProductQuestionButton
                isWithText
                isWithShortText
                className={iconButtonClassNames}
                productName={product.fullName}
                productUuid={product.uuid}
            />
        </div>
    );
};
