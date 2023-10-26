import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleComparison } from './SkeletonModuleComparison';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageComparison: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />
        <Skeleton className="mb-8 h-8 w-80 lg:h-9" />
        <SkeletonModuleComparison />
    </Webline>
);
