import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleLastVisitedProducts: FC = () => (
    <div className="flex flex-col gap-3">
        <Skeleton className="h-5 w-40 lg:h-6" />
        <Skeleton className="h-[215px]" />
    </div>
);
