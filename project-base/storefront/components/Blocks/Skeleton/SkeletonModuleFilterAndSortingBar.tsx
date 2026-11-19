import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleFilterAndSortingBar: FC = () => (
    <>
        {/* Mirrors the loaded mobile bar: the sort button and the view toggle in one row, the count is next to the heading. */}
        <div className="flex vl:hidden items-center gap-2">
            <Skeleton className="h-11 min-w-0 flex-1 sm:max-w-80" />
            <Skeleton className="ml-auto h-11 w-22" />
        </div>

        <div className="vl:flex hidden h-11 w-full items-center gap-5">
            <Skeleton className="h-5 w-24 rounded-sm" />
            <Skeleton className="h-5 w-24 rounded-sm" />
            <Skeleton className="h-5 w-24 rounded-sm" />

            <Skeleton className="ml-auto h-4 w-20 rounded-sm" />
            <Skeleton className="h-9 w-18" />
        </div>
    </>
);
