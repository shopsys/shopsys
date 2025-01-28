'use client';

import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerOrderDetail } from './SkeletonModuleCustomerOrderDetail';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageCustomerOrderDetail: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleCustomerOrderDetail />
    </Webline>
);
