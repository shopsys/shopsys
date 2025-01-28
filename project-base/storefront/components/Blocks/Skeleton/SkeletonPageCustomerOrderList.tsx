'use client';

import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerOrderList } from './SkeletonModuleCustomerOrderList';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageCustomerOrderList: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomerOrderList />
    </Webline>
);
