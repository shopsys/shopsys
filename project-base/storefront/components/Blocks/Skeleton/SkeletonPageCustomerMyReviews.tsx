import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleCustomerComplaints } from './SkeletonModuleCustomerComplaints';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonPageCustomerMyReviews: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleCustomer>
            <SkeletonModulePageHero />

            <SkeletonModuleCustomerComplaints />
        </SkeletonModuleCustomer>
    </>
);
