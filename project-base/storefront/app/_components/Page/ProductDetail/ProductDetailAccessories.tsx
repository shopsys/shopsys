import { ProductSlider } from 'app/_components/Blocks/Product/ProductSlider';
import { ProductListItem } from 'app/_components/Blocks/Product/ProductsList/ProductListItem';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export type ProductDetailAccessoriesProps = {
    accessories: TypeListedProductFragment[];
};

export async function ProductDetailAccessories({ accessories }: ProductDetailAccessoriesProps) {
    const t = await getTranslation();

    if (!accessories.length) {
        return null;
    }

    return (
        <Webline>
            <h5 className="mb-3">{t('You can also buy')}</h5>

            <ProductSlider totalItems={accessories.length} variant="default">
                {accessories.map((product, index) => (
                    <ProductListItem
                        key={product.uuid}
                        isShownInSlider
                        gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                        gtmProductListName={GtmProductListNameType.product_detail_accessories}
                        listIndex={index}
                        product={product}
                    />
                ))}
            </ProductSlider>
        </Webline>
    );
}
