import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleHeadline } from './SkeletonModuleHeadline';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerComplaintDetail: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModuleHeadline />

        <Skeleton className="h-20 rounded-xl" />
        <Skeleton className="h-56 rounded-xl" />

        <div className="bg-skeleton-less vl:grid-cols-3 grid grid-cols-1 gap-2.5 rounded-xl p-5 lg:grid-cols-2">
            <Skeleton className="h-44 rounded-xl" />
            <Skeleton className="h-44 rounded-xl" />
        </div>
    </SkeletonModuleCustomer>
);
