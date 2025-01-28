'use client';

import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerChangePassword } from './SkeletonModuleCustomerChangePassword';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageCustomerChangePassword: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomerChangePassword />
    </Webline>
);
