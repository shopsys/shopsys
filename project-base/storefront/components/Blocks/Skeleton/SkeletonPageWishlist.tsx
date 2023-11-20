import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleWishlist } from './SkeletonModuleWishlist';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageWishlist: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />
        <Skeleton className="mb-3 h-8 w-44 lg:mb-4 lg:h-9" />
        <Skeleton className="h-16" />
        <SkeletonModuleWishlist />
    </Webline>
);
