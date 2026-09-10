import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';

export const SkeletonModuleWishlist: FC = () => (
    <>
        <div className="mb-4 flex flex-col gap-2">
            <div className="flex items-center justify-between gap-2">
                <Skeleton className="h-8 w-36 lg:h-10" />
                <Skeleton className="h-9 w-10 xs:w-30 shrink-0" />
            </div>

            <div className="flex items-center justify-between gap-2 sm:justify-end">
                <Skeleton className="h-3 w-20 rounded-sm" />

                <div className="flex items-center gap-1">
                    <Skeleton className="size-8" />
                    <Skeleton className="size-8" />
                </div>
            </div>
        </div>

        <div className="relative grid grid-cols-1 xs:grid-cols-2 xxl:grid-cols-5 gap-2.5 sm:gap-x-5 sm:gap-y-6 lg:grid-cols-3 xl:grid-cols-4">
            {createEmptyArray(5).map((_, index) => (
                <SkeletonModuleProductListItem key={index} />
            ))}
        </div>
    </>
);
