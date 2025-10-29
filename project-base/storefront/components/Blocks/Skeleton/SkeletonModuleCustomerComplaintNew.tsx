import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerComplaintNew: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModulePageHero />

        <Skeleton className="h-12" />

        <Skeleton className="h-32 rounded-xl" />
        <Skeleton className="h-32 rounded-xl" />
        <Skeleton className="h-32 rounded-xl" />
    </SkeletonModuleCustomer>
);
