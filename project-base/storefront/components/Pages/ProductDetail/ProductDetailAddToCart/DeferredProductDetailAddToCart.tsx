import { SkeletonModuleProductDetailAddToCart } from 'components/Blocks/Skeleton/SkeletonModuleProductDetailAddToCart';
import { ProductDetailAddToCartProps } from 'components/Pages/ProductDetail/ProductDetailAddToCart/ProductDetailAddToCart';
import dynamic from 'next/dynamic';
import { createContext, useContext } from 'react';
import { twMergeCustom } from 'utils/twMerge';
import { useDeferredRender } from 'utils/useDeferredRender';

type AddToCartSkeletonOptions = Pick<ProductDetailAddToCartProps, 'buttonSize' | 'isInPurchaseActionsRow'>;

// The dynamic loading fallback receives no props, so the caller's size and placement reach it through context.
const AddToCartSkeletonContext = createContext<AddToCartSkeletonOptions>({});

const AddToCartLoadingSkeleton: FC = () => {
    const { buttonSize, isInPurchaseActionsRow } = useContext(AddToCartSkeletonContext);

    return <SkeletonModuleProductDetailAddToCart isInPurchaseActionsRow={isInPurchaseActionsRow} size={buttonSize} />;
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
    isInPurchaseActionsRow = false,
    ...props
}) => {
    const shouldRender = useDeferredRender('add_to_cart');

    return (
        <div className={twMergeCustom('flex w-full', isInPurchaseActionsRow && 'contents', className)}>
            <AddToCartSkeletonContext.Provider value={{ buttonSize, isInPurchaseActionsRow }}>
                {shouldRender ? (
                    <ProductDetailAddToCart
                        {...props}
                        buttonSize={buttonSize}
                        isInPurchaseActionsRow={isInPurchaseActionsRow}
                    />
                ) : (
                    <SkeletonModuleProductDetailAddToCart
                        isInPurchaseActionsRow={isInPurchaseActionsRow}
                        size={buttonSize}
                    />
                )}
            </AddToCartSkeletonContext.Provider>
        </div>
    );
};
