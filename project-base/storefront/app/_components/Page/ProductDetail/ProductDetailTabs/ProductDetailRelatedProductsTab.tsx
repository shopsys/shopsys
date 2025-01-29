import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export type ProductDetailRelatedProductsTabProps = {
    relatedProducts: TypeListedProductFragment[];
};

export const ProductDetailRelatedProductsTab: FC<ProductDetailRelatedProductsTabProps> = ({ relatedProducts }) => (
    <div className="mt-10">
        <ProductsSlider
            ariaAnchorName="product-slider-related"
            gtmProductListName={GtmProductListNameType.product_detail_related_products}
            products={relatedProducts}
        />
    </div>
);
