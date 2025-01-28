'use client';

import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerComplaintNew } from './SkeletonModuleCustomerComplaintNew';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageCustomerComplaintNew: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleCustomerComplaintNew />
    </Webline>
);
