import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerChangePassword } from './SkeletonModuleCustomerChangePassword';

export const SkeletonPageCustomerChangePassword: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomerChangePassword />
    </>
);
