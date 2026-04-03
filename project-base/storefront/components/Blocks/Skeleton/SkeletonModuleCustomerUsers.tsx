import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonCustomerUsersTable: FC = () => (
    <div className="flex flex-col gap-1">
        <Skeleton className="h-12 w-full" />
        <Skeleton className="h-12 w-full" />
        <Skeleton className="h-12 w-full" />
    </div>
);

export const SkeletonModuleCustomerUsers: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModulePageHero />

        <Skeleton className="h-9 w-36 self-center" />

        <SkeletonCustomerUsersTable />
    </SkeletonModuleCustomer>
);
