import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerChangePassword: FC = () => (
    <SkeletonModuleCustomer>
        <div className="max-w-3xl flex-1">
            <Skeleton className="mb-4 h-11" containerClassName="w-72" />

            <Skeleton className="h-[250px] w-full" />
        </div>
    </SkeletonModuleCustomer>
);
