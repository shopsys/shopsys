import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleCustomerComplaints } from './SkeletonModuleCustomerComplaints';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerComplaintList: FC = () => (
    <SkeletonModuleCustomer>
        <Skeleton className="h-8 w-72 lg:h-10" />

        <div className="flex gap-2">
            <Skeleton className="h-8 w-36" />
            <Skeleton className="h-8 w-36" />
        </div>

        <Skeleton className="h-12" />

        <SkeletonModuleCustomerComplaints />
    </SkeletonModuleCustomer>
);
