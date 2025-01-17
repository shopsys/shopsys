'use client';

import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getServerT } from 'utils/getServerTranslation';

export type ProductDetailAccessoriesProps = {
    accessories: TypeListedProductFragment[];
};

export async function ProductDetailAccessories({ accessories }: ProductDetailAccessoriesProps) {
    const t = await getServerT();

    if (!accessories.length) {
        return null;
    }

    return (
        <Webline>
            <h5 className="mb-4">{t('You can also buy')}</h5>

            <ProductsSlider
                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                gtmProductListName={GtmProductListNameType.product_detail_accessories}
                products={accessories}
            />
        </Webline>
    );
}
