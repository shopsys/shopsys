import { SkeletonBanners } from './SkeletonBanners';
import { SkeletonMagazine } from './SkeletonMagazine';
import { SkeletonPromotedCategories } from './SkeletonPromotedCategories';
import { SkeletonPromotedProducts } from './SkeletonPromotedProducts';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageHome: FC = () => (
    <Webline>
        <SkeletonBanners className="mb-14" />
        <Skeleton className="mb-3 h-8 w-72" />
        <SkeletonPromotedCategories className="mb-6" />
        <Skeleton className="mb-3 h-8 w-72" />
        <SkeletonPromotedProducts className="mb-6" />
        <SkeletonMagazine />
    </Webline>
);
