import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export type ProductDetailRelatedProductsTabProps = {
    relatedProducts: TypeListedProductFragment[];
};

export const ProductDetailRelatedProductsTab: FC<ProductDetailRelatedProductsTabProps> = ({ relatedProducts }) => (
    <ProductsSlider
        gtmProductListName={GtmProductListNameType.product_detail_related_products}
        products={relatedProducts}
    />
);
