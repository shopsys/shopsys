import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleComparison } from './SkeletonModuleComparison';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageComparison: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline>
            <Skeleton className="mb-4 h-8 w-80 lg:h-10" />

            <SkeletonModuleComparison />
        </Webline>
    </>
);
