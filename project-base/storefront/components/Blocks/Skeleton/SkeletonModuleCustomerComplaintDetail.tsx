import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonModuleCustomerComplaintDetail: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModulePageHero simple />

        <Skeleton className="h-20 rounded-card" />
        <Skeleton className="h-56 rounded-card" />

        <div className="grid grid-cols-1 vl:grid-cols-3 gap-2.5 rounded-card bg-skeleton-less p-5 lg:grid-cols-2">
            <Skeleton className="h-44 rounded-card" />
            <Skeleton className="h-44 rounded-card" />
        </div>
    </SkeletonModuleCustomer>
);
