import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { productListTwClass } from 'components/Blocks/Product/ProductsList/ProductsList';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export type SkeletonModuleProductsListProps = {
    isWithoutDescription?: boolean;
    isWithoutNavigation?: boolean;
};

export const SkeletonModuleProductsList: FC<SkeletonModuleProductsListProps> = ({
    isWithoutDescription,
    isWithoutNavigation,
}) => (
    <div>
        {!isWithoutDescription && (
            <div className="flex w-full flex-col">
                <Skeleton className="mb-5 h-11 w-1/4" />
                <Skeleton className="mb-7 h-32 w-full" />
            </div>
        )}

        {isWithoutNavigation && (
            <div className="my-7 grid gap-2 md:grid-cols-3 lg:grid-cols-4 vl:grid-cols-5">
                {createEmptyArray(5).map((_, index) => (
                    <Skeleton key={index} className="h-20 w-full" />
                ))}
            </div>
        )}

        <div className="flex flex-row items-stretch gap-5">
            <Skeleton className="h-[1000px] w-[227px]" containerClassName="hidden vl:block" />

            <div className="w-full">
                <div className="flex">
                    <div className="flex w-full flex-col">
                        <Skeleton className="mb-5 h-96 w-full" />

                        <div className="mb-10 flex flex-wrap justify-between gap-2 vl:hidden">
                            <Skeleton className="h-12 w-40" />
                            <Skeleton className="h-12 w-32" />
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
