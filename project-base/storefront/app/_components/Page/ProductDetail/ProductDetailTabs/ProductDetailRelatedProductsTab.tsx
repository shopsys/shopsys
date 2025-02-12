import { ProductsList } from 'app/_components/Blocks/Product/ProductsList/ProductsList';
// import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export type ProductDetailRelatedProductsTabProps = {
    relatedProducts: TypeListedProductFragment[];
};

export const ProductDetailRelatedProductsTab: FC<ProductDetailRelatedProductsTabProps> = ({ relatedProducts }) => (
    // <ProductsSlider
    //     gtmProductListName={GtmProductListNameType.product_detail_related_products}
    //     products={relatedProducts}
    // />

    <ProductsList
        gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
        gtmProductListName={GtmProductListNameType.product_detail_accessories}
        products={relatedProducts}
    />
);
