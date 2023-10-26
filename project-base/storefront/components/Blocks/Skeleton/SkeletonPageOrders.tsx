import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerOrders } from './SkeletonModuleCustomerOrders';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageOrders: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />
        <Skeleton className="mb-3 h-8 w-64 lg:mb-4 lg:h-9" containerClassName="flex justify-center" />
        <SkeletonModuleCustomerOrders />
    </Webline>
);
