import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductQuestionButton } from 'components/Blocks/Product/ButtonsAction/ProductQuestionButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

export type ProductDetailSecondaryActionsProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailSecondaryActions: FC<ProductDetailSecondaryActionsProps> = ({ product }) => {
    const { isProductInComparison, toggleProductInComparison } = useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    const iconButtonClassNames = 'min-w-0 flex-col items-center gap-1 text-center sm:shrink-0 sm:flex-row sm:text-left';

    return (
        <div className="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap sm:items-center sm:gap-x-5 sm:gap-y-3">
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
