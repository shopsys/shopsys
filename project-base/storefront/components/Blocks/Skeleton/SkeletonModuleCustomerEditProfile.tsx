import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleCustomerEditProfile: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModulePageHero />

        <Skeleton className="h-[1000px] rounded-xl" />
    </SkeletonModuleCustomer>
);
