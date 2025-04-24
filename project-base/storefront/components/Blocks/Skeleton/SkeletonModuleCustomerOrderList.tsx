import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleCustomerOrders } from './SkeletonModuleCustomerOrders';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerOrderList: FC = () => (
    <SkeletonModuleCustomer>
        <Skeleton className="h-8 w-72 lg:h-10" />

        <SkeletonModuleCustomerOrders />
    </SkeletonModuleCustomer>
);
