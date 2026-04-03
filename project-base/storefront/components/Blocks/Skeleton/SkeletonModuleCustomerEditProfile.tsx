import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonModuleCustomerEditProfile: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModulePageHero />

        <Skeleton className="h-[1000px] rounded-xl" />
    </SkeletonModuleCustomer>
);
