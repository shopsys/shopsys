import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
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

    return (
        <div className="flex flex-wrap gap-x-4 gap-y-1">
            <ProductCompareButton
                isWithText
                isProductInComparison={isProductInComparison(product.uuid)}
                productName={product.fullName}
                toggleProductInComparison={() =>
                    toggleProductInComparison(product, GtmProductListNameType.product_detail)
                }
            />
            <ProductWishlistButton
                isWithText
                isProductInWishlist={isProductInWishlist(product.uuid)}
                productName={product.fullName}
                toggleProductInWishlist={() => toggleProductInWishlist(product, GtmProductListNameType.product_detail)}
            />
        </div>
    );
};
