import {
    ProductDetailAccessories,
    ProductDetailAccessoriesProps,
} from 'components/Pages/ProductDetail/ProductDetailAccessories/ProductDetailAccessories';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
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
        <div>
            <div className="text-xl font-bold">{t('You can also buy')}</div>
            {shouldRender ? (
                <ProductDetailAccessories accessories={accessories} />
            ) : (
                <ProductsSliderPlaceholder products={accessories} />
            )}
        </div>
    );
};
