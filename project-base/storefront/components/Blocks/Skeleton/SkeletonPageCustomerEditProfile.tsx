import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerEditProfile } from './SkeletonModuleCustomerEditProfile';

export const SkeletonPageCustomerEditProfile: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomerEditProfile />
    </>
);
