import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleCustomerOrders } from './SkeletonModuleCustomerOrders';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonModuleCustomerOrderList: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModulePageHero />

        <SkeletonModuleCustomerOrders />
    </SkeletonModuleCustomer>
);
