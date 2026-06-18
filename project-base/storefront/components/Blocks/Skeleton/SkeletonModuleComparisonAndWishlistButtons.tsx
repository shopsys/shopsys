import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleComparisonAndWishlistButtons: FC = () => (
    <div className="grid grid-cols-3 gap-2 sm:flex sm:flex-nowrap sm:items-center sm:gap-x-4 sm:overflow-hidden">
        <div className="flex min-w-0 flex-col items-center gap-1 sm:flex-row sm:gap-2">
            <Skeleton className="size-6 shrink-0 rounded-full" />
            <Skeleton className="h-5 w-20 max-w-full" />
        </div>
        <div className="flex min-w-0 flex-col items-center gap-1 sm:flex-row sm:gap-2">
            <Skeleton className="size-6 shrink-0 rounded-full" />
            <Skeleton className="h-5 w-20 max-w-full" />
        </div>
        <div className="flex min-w-0 flex-col items-center gap-1 sm:flex-row sm:gap-2">
            <Skeleton className="size-6 shrink-0 rounded-full" />
            <Skeleton className="h-5 w-16 xs:w-24 max-w-full" />
        </div>
    </div>
);
