'use client';

import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerComplaintDetail } from './SkeletonModuleCustomerComplaintDetail';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageCustomerComplaintDetail: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleCustomerComplaintDetail />
    </Webline>
);
