import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerUsers } from './SkeletonModuleCustomerUsers';

export const SkeletonPageCustomerUsers: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomerUsers />
    </>
);
