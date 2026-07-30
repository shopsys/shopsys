import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import type { ProductItemProps } from 'components/Blocks/Product/ProductsList/ProductListItem';
import {
    getProductsSliderTwClass,
    type ProductsSliderProps,
    VISIBLE_SLIDER_ITEMS,
} from 'components/Blocks/Product/ProductsSlider';
import { twJoin } from 'tailwind-merge';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';

type SkeletonModuleProductSliderProps = {
    isHeadingHidden?: boolean;
    productItemProps?: Pick<ProductItemProps, 'size' | 'visibleItemsConfig'>;
    variant?: ProductsSliderProps['variant'];
    visibleSliderItems?: ProductsSliderProps['visibleSliderItems'];
};

export const SkeletonModuleProductSlider: FC<SkeletonModuleProductSliderProps> = ({
    isHeadingHidden,
    productItemProps,
    variant = 'default',
    visibleSliderItems = VISIBLE_SLIDER_ITEMS,
}) => (
    <div className="flex flex-col gap-3">
        {!isHeadingHidden && <Skeleton className="h-7 w-40 lg:h-8" />}

        <div className="relative">
            <div className="w-full">
                <div
                    className={twJoin([
                        'hide-scrollbar grid snap-x snap-mandatory grid-flow-col overflow-x-auto overscroll-x-contain',
                        getProductsSliderTwClass(variant),
                    ])}
                >
                    {createEmptyArray(visibleSliderItems).map((_, index) => (
                        <div key={index} className="mr-2 snap-center last:mr-0 md:mr-4 md:snap-start">
                            <SkeletonModuleProductListItem
                                isBasketPopup={variant === 'basketPopup'}
                                size={productItemProps?.size}
                                visibleItemsConfig={productItemProps?.visibleItemsConfig}
                            />
                        </div>
                    ))}
                </div>
            </div>
        </div>
    </div>
);
