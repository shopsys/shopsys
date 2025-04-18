import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleHeadline } from './SkeletonModuleHeadline';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerOrderDetail: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModuleHeadline />

        <Skeleton className="h-21 rounded-xl" />
        <Skeleton className="h-32 rounded-xl" />
        <Skeleton className="h-22 rounded-xl" />

        <Skeleton className="h-64" />

        <div className="bg-skeleton-less vl:grid-cols-3 grid grid-cols-1 gap-2.5 rounded-xl p-5 lg:grid-cols-2">
            <Skeleton className="h-44 rounded-xl" />
            <Skeleton className="h-44 rounded-xl" />
            <Skeleton className="h-44 rounded-xl" />
        </div>
    </SkeletonModuleCustomer>
);
