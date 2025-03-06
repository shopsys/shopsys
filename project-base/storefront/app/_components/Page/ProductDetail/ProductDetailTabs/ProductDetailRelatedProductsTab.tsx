import { ProductSlider } from 'app/_components/Blocks/Product/ProductSlider';
import { ProductListItem } from 'app/_components/Blocks/Product/ProductsList/ProductListItem';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export type ProductDetailRelatedProductsTabProps = {
    relatedProducts: TypeListedProductFragment[];
};

export const ProductDetailRelatedProductsTab: FC<ProductDetailRelatedProductsTabProps> = ({ relatedProducts }) => (
    <ProductSlider totalItems={relatedProducts.length} variant="default">
        {relatedProducts.map((product, index) => (
            <ProductListItem
                key={product.uuid}
                isShownInSlider
                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                gtmProductListName={GtmProductListNameType.product_detail_related_products}
                listIndex={index}
                product={product}
            />
        ))}
    </ProductSlider>
);
