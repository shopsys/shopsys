import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonCustomerUsersTable: FC = () => (
    <div className="flex flex-col">
        <Skeleton className="mb-2 hidden h-10 w-full sm:block" />
        {[0, 1, 2].map((index) => (
            <div
                key={index}
                className="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-4 border-border-less/50 border-b px-2 py-4 sm:flex sm:flex-row"
            >
                <div className="col-span-2 flex flex-col gap-2 sm:w-1/2">
                    <Skeleton className="h-4 w-36" />
                    <Skeleton className="h-3 w-48" />
                </div>
                <Skeleton className="h-6 w-28" />
                <Skeleton className="ml-auto h-8 w-18" />
            </div>
        ))}
    </div>
);

export const SkeletonModuleCustomerUsers: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModulePageHero />

        <Skeleton className="h-9 w-36 self-center" />

        <SkeletonCustomerUsersTable />
    </SkeletonModuleCustomer>
);
