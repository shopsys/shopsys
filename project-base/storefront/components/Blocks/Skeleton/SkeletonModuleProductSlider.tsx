import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export type SkeletonModuleProductsListProps = {
    isWithoutNavigation?: boolean;
};

export const SkeletonModuleProductSlider: FC<SkeletonModuleProductsListProps> = ({ isWithoutNavigation }) => (
    <div className="flex flex-row items-stretch gap-5 h-[500px]">
        <div className="w-full">
            {!isWithoutNavigation && (
                <div className="mb-7 flex flex-wrap gap-2">
                    {createEmptyArray(4).map((_, index) => (
                        <div
                            key={index}
                            className="h-14 sm:w-[calc(50%-4px)] md:w-[calc(33.33%-8px)] lg:h-20 xl:w-[calc(25%-8px)] w-full"
                        >
                            <SkeletonModuleProductListItem />
                        </div>
                    ))}
                </div>
            )}
        </div>
    </div>
);
