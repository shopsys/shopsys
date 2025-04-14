import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerComplaintNew } from './SkeletonModuleCustomerComplaintNew';

export const SkeletonPageCustomerComplaintNew: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleCustomerComplaintNew />
    </>
);
