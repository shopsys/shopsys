import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { ListedProductFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';

type ProductDetailAccessoriesProps = {
    accessories: ListedProductFragmentApi[];
};

export const ProductDetailAccessories: FC<ProductDetailAccessoriesProps> = ({ accessories }) => {
    const { t } = useTranslation();

    return (
        <div>
            <div className="text-xl font-bold">{t('You can also buy')}</div>
            <ProductsSlider
                products={accessories}
                gtmProductListName={GtmProductListNameType.product_detail_accessories}
                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
            />
        </div>
    );
};
