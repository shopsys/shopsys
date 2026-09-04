import { SkeletonModuleProductDetailSecondaryActions } from 'components/Blocks/Skeleton/SkeletonModuleProductDetailSecondaryActions';
import { ProductDetailSecondaryActionsProps } from 'components/Pages/ProductDetail/ProductDetailSecondaryActions/ProductDetailSecondaryActions';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const ProductDetailSecondaryActions = dynamic(
    () => import('./ProductDetailSecondaryActions').then((component) => component.ProductDetailSecondaryActions),
    {
        ssr: false,
        loading: () => <SkeletonModuleProductDetailSecondaryActions />,
    },
);

export const DeferredProductDetailSecondaryActions: FC<ProductDetailSecondaryActionsProps> = ({ product }) => {
    const shouldRender = useDeferredRender('secondary_actions');

    return shouldRender ? (
        <ProductDetailSecondaryActions product={product} />
    ) : (
        <SkeletonModuleProductDetailSecondaryActions />
    );
};
