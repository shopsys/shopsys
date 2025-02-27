import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerComplaintList: FC = () => (
    <SkeletonModuleCustomer>
        <div className="flex flex-1 flex-col">
            <Skeleton className="mb-4 h-11 w-72" />
            <Skeleton className="mb-4 h-8 w-36" />
            <Skeleton className="mb-4 h-10 w-full" />

            <Skeleton className="mb-5 h-36 w-full" />
            <Skeleton className="mb-5 h-36 w-full" />
            <Skeleton className="mb-5 h-36 w-full" />
        </div>
    </SkeletonModuleCustomer>
);
