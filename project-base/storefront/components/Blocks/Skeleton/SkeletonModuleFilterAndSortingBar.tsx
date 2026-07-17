import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleFilterAndSortingBar: FC = () => (
    <div className="vl:flex hidden w-full items-center justify-between">
        <div className="flex gap-4">
            <Skeleton className="h-9 w-32" />
            <Skeleton className="h-9 w-32" />
            <Skeleton className="h-9 w-32" />
        </div>

        <Skeleton className="h-4 w-20 rounded-sm" />
    </div>
);
