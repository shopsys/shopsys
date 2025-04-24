import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleWishlist } from './SkeletonModuleWishlist';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageWishlist: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline>
            <Skeleton className="mb-4 h-8 w-44 lg:h-10" />
            <Skeleton className="mb-2 h-9 w-48" />

            <SkeletonModuleWishlist />
        </Webline>
    </>
);
