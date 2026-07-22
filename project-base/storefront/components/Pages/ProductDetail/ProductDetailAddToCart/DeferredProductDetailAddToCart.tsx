import { SkeletonModuleProductDetailAddToCart } from 'components/Blocks/Skeleton/SkeletonModuleProductDetailAddToCart';
import { ProductDetailAddToCartProps } from 'components/Pages/ProductDetail/ProductDetailAddToCart/ProductDetailAddToCart';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const ProductDetailAddToCart = dynamic(
    () => import('./ProductDetailAddToCart').then((component) => component.ProductDetailAddToCart),
    {
        ssr: false,
        loading: () => <SkeletonModuleProductDetailAddToCart />,
    },
);

export const DeferredProductDetailAddToCart: FC<ProductDetailAddToCartProps> = (props) => {
    const shouldRender = useDeferredRender('add_to_cart');

    return (
        <div className="w-full">
            {shouldRender ? (
                <ProductDetailAddToCart {...props} />
            ) : (
                <div className="w-full sm:max-w-60">
                    <SkeletonModuleProductDetailAddToCart size={props.buttonSize} />
                </div>
            )}
        </div>
    );
};
