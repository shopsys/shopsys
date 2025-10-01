import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerEditProfile: FC = () => (
    <SkeletonModuleCustomer>
        <Skeleton className="h-8 w-72 lg:h-10" />

        <Skeleton className="h-[1000px] rounded-xl" />
    </SkeletonModuleCustomer>
);
