import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleWishlist } from './SkeletonModuleWishlist';

export const SkeletonPageWishlist: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline>
            <SkeletonModuleWishlist />
        </Webline>
    </>
);
