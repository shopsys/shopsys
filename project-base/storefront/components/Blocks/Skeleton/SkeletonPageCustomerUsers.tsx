'use client';

import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerUsers } from './SkeletonModuleCustomerUsers';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageCustomerUsers: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomerUsers />
    </Webline>
);
