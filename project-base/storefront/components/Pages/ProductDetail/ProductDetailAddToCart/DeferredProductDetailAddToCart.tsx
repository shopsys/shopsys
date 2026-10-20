import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModuleProductDetailAddToCart } from 'components/Blocks/Skeleton/SkeletonModuleProductDetailAddToCart';
import { ProductDetailAddToCartProps } from 'components/Pages/ProductDetail/ProductDetailAddToCart/ProductDetailAddToCart';
import dynamic from 'next/dynamic';
import { twMergeCustom } from 'utils/twMerge';
import { useDeferredRender } from 'utils/useDeferredRender';

const ProductDetailAddToCart = dynamic(
    () => import('./ProductDetailAddToCart').then((component) => component.ProductDetailAddToCart),
    {
        ssr: false,
        loading: () => <Skeleton className="min-h-[inherit] w-full" />,
    },
);

type DeferredProductDetailAddToCartProps = ProductDetailAddToCartProps & {
    className?: string;
};

export const DeferredProductDetailAddToCart: FC<DeferredProductDetailAddToCartProps> = ({
    buttonSize = 'xlarge',
    className,
    isFullWidth = false,
    ...props
}) => {
    const shouldRender = useDeferredRender('add_to_cart');

    return (
        <div
            className={twMergeCustom(
                'flex w-full',
                buttonSize !== 'xlarge' && 'min-h-9',
                buttonSize === 'large' && 'sm:min-h-10',
                buttonSize === 'xlarge' && 'min-h-14',
                !isFullWidth && 'sm:max-w-60',
                className,
            )}
        >
            {shouldRender ? (
                <ProductDetailAddToCart {...props} buttonSize={buttonSize} isFullWidth={isFullWidth} />
            ) : (
                <SkeletonModuleProductDetailAddToCart isFullWidth={isFullWidth} size={buttonSize} />
            )}
        </div>
    );
};
