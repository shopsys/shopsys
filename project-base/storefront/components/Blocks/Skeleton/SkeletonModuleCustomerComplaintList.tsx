import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleCustomerComplaints } from './SkeletonModuleCustomerComplaints';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonModuleCustomerComplaintList: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModulePageHero />

        <div className="flex justify-center gap-2">
            <Skeleton className="h-[36px] w-36" />
            <Skeleton className="h-[36px] w-36" />
        </div>

        <Skeleton className="h-12" />

        <SkeletonModuleCustomerComplaints />
    </SkeletonModuleCustomer>
);
