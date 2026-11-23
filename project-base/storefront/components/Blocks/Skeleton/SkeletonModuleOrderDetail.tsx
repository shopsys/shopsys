import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonModuleOrderDetail: FC = () => (
    <>
        <SkeletonModulePageHero simple />

        <Skeleton className="h-21 rounded-card" />
        <Skeleton className="h-32 rounded-card" />
        <Skeleton className="h-22 rounded-card" />

        <Skeleton className="h-64 rounded-card" />

        <div className="grid grid-cols-1 vl:grid-cols-3 gap-2.5 rounded-card bg-skeleton-less p-5 lg:grid-cols-2">
            <Skeleton className="h-44 rounded-card" />
            <Skeleton className="h-44 rounded-card" />
            <Skeleton className="h-44 rounded-card" />
        </div>
    </>
);
