import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleHeadline } from './SkeletonModuleHeadline';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerChangePassword: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModuleHeadline />

        <div className="max-w-3xl">
            <Skeleton className="h-[250px] rounded-xl" />
        </div>
    </SkeletonModuleCustomer>
);
