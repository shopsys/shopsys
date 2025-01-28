'use client';

import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerEditProfile } from './SkeletonModuleCustomerEditProfile';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageCustomerEditProfile: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomerEditProfile />
    </Webline>
);
