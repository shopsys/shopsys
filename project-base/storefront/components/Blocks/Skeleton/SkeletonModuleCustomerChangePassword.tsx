import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleHeadline } from './SkeletonModuleHeadline';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerChangePassword: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModuleHeadline />

        <Skeleton className="h-[250px] rounded-xl" />
    </SkeletonModuleCustomer>
);
