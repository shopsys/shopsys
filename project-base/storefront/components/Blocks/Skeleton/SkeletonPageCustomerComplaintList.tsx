import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomerComplaintList } from './SkeletonModuleCustomerComplaintList';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageCustomerComplaintList: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleCustomerComplaintList />
    </Webline>
);
