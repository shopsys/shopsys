import { SkeletonModuleProductDetailAddToCart } from 'components/Blocks/Skeleton/SkeletonModuleProductDetailAddToCart';
import { ProductDetailAddToCartProps } from 'components/Pages/ProductDetail/ProductDetailAddToCart/ProductDetailAddToCart';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const ProductDetailAddToCart = dynamic(
    () => import('./ProductDetailAddToCart').then((component) => ({
        default: component.ProductDetailAddToCart
    })),
    {
        ssr: false,
        loading: () => <SkeletonModuleProductDetailAddToCart />,
    },
);

export const DeferredProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({ product }) => {
    const shouldRender = useDeferredRender('add_to_cart');

    return shouldRender ? <ProductDetailAddToCart product={product} /> : <SkeletonModuleProductDetailAddToCart />;
};
