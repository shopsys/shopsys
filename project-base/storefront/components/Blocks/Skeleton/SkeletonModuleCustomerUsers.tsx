import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonCustomerUsersTable: FC = () => (
    <div className="flex flex-col gap-1">
        <Skeleton className="h-12 w-full" />
        <Skeleton className="h-12 w-full" />
        <Skeleton className="h-12 w-full" />
    </div>
);

export const SkeletonModuleCustomerUsers: FC = () => (
    <SkeletonModuleCustomer>
        <Skeleton className="h-8 w-72 lg:h-10" />

        <Skeleton className="h-9 w-36" />

        <SkeletonCustomerUsersTable />
    </SkeletonModuleCustomer>
);
