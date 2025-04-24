import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerComplaintDetail } from './SkeletonModuleCustomerComplaintDetail';

export const SkeletonPageCustomerComplaintDetail: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleCustomerComplaintDetail />
    </>
);
