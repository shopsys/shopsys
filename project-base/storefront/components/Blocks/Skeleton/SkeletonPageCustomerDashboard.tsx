import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';

export const SkeletonPageCustomerDashboard: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />
        <SkeletonModuleCustomer>
            <Skeleton className="h-10 w-48" />
            <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,19rem),1fr))] gap-4">
                <Skeleton className="h-64 rounded-xl" />
                <Skeleton className="h-64 rounded-xl" />
            </div>
        </SkeletonModuleCustomer>
    </>
);
