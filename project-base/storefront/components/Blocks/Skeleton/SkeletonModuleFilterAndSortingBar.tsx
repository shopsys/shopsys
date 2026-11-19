import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleFilterAndSortingBar: FC = () => (
    <>
        {/* Mirrors the loaded mobile bar: the sort button and the product count with the view toggle. */}
        <div className="flex vl:hidden flex-col justify-between gap-2.5 sm:flex-row sm:items-center">
            <Skeleton className="h-11 w-full sm:w-80" />
            <Skeleton className="h-8 w-full sm:w-33" />
        </div>

        <div className="vl:flex hidden w-full items-center justify-between gap-2">
            <div className="flex gap-4">
                <Skeleton className="h-9 w-32" />
                <Skeleton className="h-9 w-32" />
                <Skeleton className="h-9 w-32" />
            </div>

            <Skeleton className="ml-auto h-4 w-20 rounded-sm" />
            <Skeleton className="m-1.5 size-5 rounded-sm" />
            <Skeleton className="m-1.5 size-5 rounded-sm" />
        </div>
    </>
);
