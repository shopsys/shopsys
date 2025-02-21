import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerComplaintDetail: FC = () => (
    <SkeletonModuleCustomer>
        <div className="flex w-full flex-col gap-4">
            <Skeleton className="mb-5 h-11 w-80" />
            <Skeleton className="h-16 w-full" />
            <Skeleton className="h-80 w-full" />

            <div className="vl:grid vl:grid-cols-3 my-6 flex w-full flex-col gap-6">
                <Skeleton className="h-24 w-full" />
                <Skeleton className="h-24 w-full" />
            </div>
        </div>
    </SkeletonModuleCustomer>
);
