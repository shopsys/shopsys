import { SkeletonModuleProductDetailAddToCart } from 'components/Blocks/Skeleton/SkeletonModuleProductDetailAddToCart';
import { ProductDetailAddToCartProps } from 'components/Pages/ProductDetail/ProductDetailAddToCart/ProductDetailAddToCart';
import dynamic from 'next/dynamic';
import { createContext, useContext } from 'react';
import { twMergeCustom } from 'utils/twMerge';
import { useDeferredRender } from 'utils/useDeferredRender';

type AddToCartSkeletonOptions = Pick<ProductDetailAddToCartProps, 'buttonSize' | 'isFullWidth'>;

// The dynamic loading fallback receives no props, so the caller's size and width reach it through context.
const AddToCartSkeletonContext = createContext<AddToCartSkeletonOptions>({});

const AddToCartLoadingSkeleton: FC = () => {
    const { buttonSize, isFullWidth } = useContext(AddToCartSkeletonContext);

    return <SkeletonModuleProductDetailAddToCart isFullWidth={isFullWidth} size={buttonSize} />;
};

const ProductDetailAddToCart = dynamic(
    () => import('./ProductDetailAddToCart').then((component) => component.ProductDetailAddToCart),
    {
        ssr: false,
        loading: () => <AddToCartLoadingSkeleton />,
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
        <div className={twMergeCustom('flex w-full', !isFullWidth && 'sm:max-w-60', className)}>
            <AddToCartSkeletonContext.Provider value={{ buttonSize, isFullWidth }}>
                {shouldRender ? (
                    <ProductDetailAddToCart {...props} buttonSize={buttonSize} isFullWidth={isFullWidth} />
                ) : (
                    <SkeletonModuleProductDetailAddToCart isFullWidth={isFullWidth} size={buttonSize} />
                )}
            </AddToCartSkeletonContext.Provider>
        </div>
    );
};
