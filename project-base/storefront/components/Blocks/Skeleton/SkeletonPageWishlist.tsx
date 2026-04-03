import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleWishlist } from './SkeletonModuleWishlist';

export const SkeletonPageWishlist: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline>
            <Skeleton className="mb-4 h-8 w-44 lg:h-10" />

            <SkeletonModuleWishlist />
        </Webline>
    </>
);
