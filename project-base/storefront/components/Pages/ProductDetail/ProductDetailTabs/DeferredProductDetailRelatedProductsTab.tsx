import { ProductDetailRelatedProductsTabProps } from './ProductDetailRelatedProductsTab';
import { ProductDetailRelatedProductsTabPlaceholder } from './ProductDetailRelatedProductsTabPlaceholder';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const ProductDetailRelatedProductsTab = dynamic(
    () => import('./ProductDetailRelatedProductsTab').then((component) => ({
        default: component.ProductDetailRelatedProductsTab
    })),
    {
        ssr: false,
    },
);

export const DeferredProductDetailRelatedProductsTab: FC<ProductDetailRelatedProductsTabProps> = (props) => {
    const shouldRender = useDeferredRender('related_products_tab');

    return shouldRender ? (
        <ProductDetailRelatedProductsTab {...props} />
    ) : (
        <ProductDetailRelatedProductsTabPlaceholder {...props} />
    );
};
