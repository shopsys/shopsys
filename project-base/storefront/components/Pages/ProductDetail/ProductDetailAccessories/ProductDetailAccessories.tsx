import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export type ProductDetailAccessoriesProps = {
    accessories: TypeListedProductFragment[];
};

export const ProductDetailAccessories: FC<ProductDetailAccessoriesProps> = ({ accessories }) => (
    <ProductsSlider
        gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
        gtmProductListName={GtmProductListNameType.product_detail_accessories}
        products={accessories}
    />
);
