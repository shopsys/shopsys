import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerOrderDetail } from './SkeletonModuleCustomerOrderDetail';

export const SkeletonPageCustomerOrderDetail: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleCustomerOrderDetail />
    </>
);
