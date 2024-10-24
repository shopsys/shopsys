import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { productListTwClass } from 'components/Blocks/Product/ProductsList/ProductsList';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export type SkeletonModuleProductsListProps = {
    isWithoutDescription?: boolean;
    isWithoutNavigation?: boolean;
    isWithoutBestsellers?: boolean;
};

export const SkeletonModuleProductsList: FC<SkeletonModuleProductsListProps> = ({
    isWithoutDescription,
    isWithoutNavigation,
    isWithoutBestsellers = false,
}) => (
    <div>
        {!isWithoutDescription && (
            <div className="flex w-full flex-col">
                <Skeleton className="mb-5 h-10 w-1/4" />
                <Skeleton className="mb-7 h-32 w-full" />
            </div>
        )}

        {!isWithoutNavigation && (
            <div className="mb-7 grid gap-2 md:grid-cols-3 lg:grid-cols-4 vl:grid-cols-5">
                {createEmptyArray(5).map((_, index) => (
                    <Skeleton key={index} className="h-20 w-full" />
                ))}
            </div>
        )}

        <div className="flex flex-row items-stretch vl:gap-5">
            <Skeleton className="hidden h-[1000px] w-[227px] vl:block" />

            <div className="w-full">
                <div className="flex">
                    <div className="flex w-full flex-col">
                        {!isWithoutBestsellers && <Skeleton className="mb-5 h-96 w-full" />}

                        <div className="flex flex-col justify-between gap-2 sm:flex-row vl:hidden">
                            <Skeleton className="h-8 w-full sm:w-40" />
                            <Skeleton className="h-8 w-full sm:w-40" />
                        </div>

                        <div className="hidden items-center justify-between vl:flex">
                            <Skeleton className="h-9 w-24" containerClassName="flex gap-3" count={3} />
                            <Skeleton className="h-4 w-20" />
                        </div>

                        <div className={productListTwClass}>
                            {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                                <SkeletonModuleProductListItem key={index} />
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
);
