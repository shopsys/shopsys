import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerComplaintNew: FC = () => (
    <SkeletonModuleCustomer>
        <div className="flex flex-1 flex-col">
            <Skeleton className="mb-4 h-11" containerClassName="w-72" />
            <Skeleton className="mb-4 h-10 w-full" />

            <Skeleton className="mb-5 h-36 w-full" />
            <Skeleton className="mb-5 h-36 w-full" />
            <Skeleton className="mb-5 h-36 w-full" />
        </div>
    </SkeletonModuleCustomer>
);
