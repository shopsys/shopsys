import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerEditProfile: FC = () => (
    <SkeletonModuleCustomer>
        <Skeleton className="h-8 w-72 lg:h-10" />

        <div className="max-w-3xl">
            <Skeleton className="h-[1000px] rounded-xl" />
        </div>
    </SkeletonModuleCustomer>
);
