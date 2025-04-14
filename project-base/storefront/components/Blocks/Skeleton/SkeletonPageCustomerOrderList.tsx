import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerOrderList } from './SkeletonModuleCustomerOrderList';

export const SkeletonPageCustomerOrderList: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomerOrderList />
    </>
);
