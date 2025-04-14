import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerComplaintList } from './SkeletonModuleCustomerComplaintList';

export const SkeletonPageCustomerComplaintList: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomerComplaintList />
    </>
);
