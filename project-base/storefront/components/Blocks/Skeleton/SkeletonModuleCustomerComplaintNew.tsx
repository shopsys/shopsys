import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleHeadline } from './SkeletonModuleHeadline';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerComplaintNew: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModuleHeadline />

        <Skeleton className="h-12" />

        <Skeleton className="h-32 rounded-xl" />
        <Skeleton className="h-32 rounded-xl" />
        <Skeleton className="h-32 rounded-xl" />
    </SkeletonModuleCustomer>
);
