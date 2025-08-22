import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VISIBLE_SLIDER_ITEMS } from 'components/Blocks/Product/ProductsSlider';
import { twJoin } from 'tailwind-merge';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export type SkeletonModuleProductsListProps = {
    isWithSimpleCards?: boolean;
};

export const SkeletonModuleProductSlider: FC<SkeletonModuleProductsListProps> = ({ isWithSimpleCards }) => (
    <div className="flex flex-col gap-3">
        <Skeleton className="h-5 w-40 lg:h-6" />

        <div className="relative">
            <div className="w-full">
                <div
                    className={twJoin([
                        'hide-scrollbar grid snap-x snap-mandatory grid-flow-col overflow-x-auto overscroll-x-contain',
                        'vl:auto-cols-[25%] auto-cols-[225px] sm:auto-cols-[60%] md:auto-cols-[45%] lg:auto-cols-[30%] xl:auto-cols-[20%]',
                        !isWithSimpleCards && 'vl:auto-cols-[25%]',
                    ])}
                >
                    {createEmptyArray(VISIBLE_SLIDER_ITEMS).map((_, index) => (
                        <div key={index} className="mr-2 snap-center last:mr-0 md:mr-4 md:snap-start">
                            <SkeletonModuleProductListItem isSimpleCard={isWithSimpleCards} />
                        </div>
                    ))}
                </div>
            </div>
        </div>
    </div>
);
