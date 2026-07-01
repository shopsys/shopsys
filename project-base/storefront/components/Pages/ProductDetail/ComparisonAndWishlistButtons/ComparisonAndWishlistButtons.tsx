import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductQuestionButton } from 'components/Blocks/Product/ProductQuestionButton';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

export type ComparisonAndWishlistButtonsProps = {
    product: TypeProductDetailFragment;
};

export const ComparisonAndWishlistButtons: FC<ComparisonAndWishlistButtonsProps> = ({ product }) => {
    const { isProductInComparison, toggleProductInComparison } = useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    const iconButtonClassNames = 'min-w-0 flex-col items-center gap-1 text-center sm:flex-row sm:gap-2 sm:text-left';

    return (
        <div className="grid grid-cols-3 gap-2 sm:flex sm:flex-nowrap sm:items-center sm:gap-x-4 sm:overflow-hidden">
            <ProductCompareButton
                isWithText
                isWithShortText
                className={iconButtonClassNames}
                isProductInComparison={isProductInComparison(product.uuid)}
                productName={product.fullName}
                toggleProductInComparison={() =>
                    toggleProductInComparison(product, GtmProductListNameType.product_detail)
                }
            />
            <ProductWishlistButton
                isWithText
                isWithShortText
                className={iconButtonClassNames}
                isProductInWishlist={isProductInWishlist(product.uuid)}
                productName={product.fullName}
                toggleProductInWishlist={() => toggleProductInWishlist(product, GtmProductListNameType.product_detail)}
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
