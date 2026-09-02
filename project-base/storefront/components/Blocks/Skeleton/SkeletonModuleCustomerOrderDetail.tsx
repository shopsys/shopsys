import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleOrderDetail } from './SkeletonModuleOrderDetail';

export const SkeletonModuleCustomerOrderDetail: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModuleOrderDetail />
    </SkeletonModuleCustomer>
);
