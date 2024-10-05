import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { VISIBLE_SLIDER_ITEMS } from 'components/Blocks/Product/ProductsSlider';
import { twJoin } from 'tailwind-merge';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export type SkeletonModuleProductsListProps = {
    isWithSimpleCards?: boolean;
};

export const SkeletonModuleProductSlider: FC<SkeletonModuleProductsListProps> = ({ isWithSimpleCards }) => (
    <div className="relative">
        <div className="w-full">
            <div
                className={twJoin([
                    "grid snap-x snap-mandatory auto-cols-[80%] grid-flow-col overflow-x-auto overscroll-x-contain [-ms-overflow-style:'none'] [scrollbar-width:'none'] md:auto-cols-[45%] lg:auto-cols-[30%] [&::-webkit-scrollbar]:hidden",
                    !isWithSimpleCards && 'vl:auto-cols-[25%]',
                ])}
            >
                {createEmptyArray(VISIBLE_SLIDER_ITEMS).map((_, index) => (
                    <div key={index}>
                        <SkeletonModuleProductListItem isSimpleCard={isWithSimpleCards} />
                    </div>
                ))}
            </div>
        </div>
    </div>
);
