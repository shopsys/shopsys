import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerEditProfile } from './SkeletonModuleCustomerEditProfile';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageCustomerEditProfile: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleCustomerEditProfile />
    </Webline>
);
