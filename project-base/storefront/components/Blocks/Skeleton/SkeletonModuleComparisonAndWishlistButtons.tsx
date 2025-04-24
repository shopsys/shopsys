import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleComparisonAndWishlistButtons: FC = () => (
    <div className="flex flex-wrap gap-x-4 gap-y-1">
        <Skeleton className="h-6 w-32" />
        <Skeleton className="h-6 w-40" />
    </div>
);
