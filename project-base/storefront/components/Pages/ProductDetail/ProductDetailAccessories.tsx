import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import useTranslation from 'next-translate/useTranslation';

type ProductDetailAccessoriesProps = {
    accessories: ListedProductFragment[];
};

export const ProductDetailAccessories: FC<ProductDetailAccessoriesProps> = ({ accessories }) => {
    const { t } = useTranslation();

    return (
        <div>
            <div className="text-xl font-bold">{t('You can also buy')}</div>
            <ProductsSlider
                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                gtmProductListName={GtmProductListNameType.product_detail_accessories}
                products={accessories}
            />
        </div>
    );
};
