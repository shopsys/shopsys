import { Webline } from 'components/Layout/Webline/Webline';
import {
    ProductDetailAccessories,
    ProductDetailAccessoriesProps,
} from 'components/Pages/ProductDetail/ProductDetailAccessories/ProductDetailAccessories';
import dynamic from 'next/dynamic';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useDeferredRender } from 'utils/useDeferredRender';

const ProductsSliderPlaceholder = dynamic(() =>
    import('components/Blocks/Product/ProductsSliderPlaceholder').then(
        (component) => component.ProductsSliderPlaceholder,
    ),
);

export const DeferredProductDetailAccessories: FC<ProductDetailAccessoriesProps> = ({ accessories }) => {
    const { t } = useTranslation();
    const shouldRender = useDeferredRender('accessories');

    if (!accessories.length) {
        return null;
    }

    return (
        <Webline>
            <h2 className="h5 mb-3">{t('You can also buy')}</h2>

            {shouldRender ? (
                <ProductDetailAccessories accessories={accessories} />
            ) : (
                <ProductsSliderPlaceholder products={accessories} />
            )}
        </Webline>
    );
};
