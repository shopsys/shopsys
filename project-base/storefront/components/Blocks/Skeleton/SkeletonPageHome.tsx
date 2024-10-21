import { SkeletonModuleBanners } from './SkeletonModuleBanners';
import { SkeletonModuleMagazine } from './SkeletonModuleMagazine';
import { SkeletonModulePromotedCategories } from './SkeletonModulePromotedCategories';
import { SkeletonModulePromotedProducts } from './SkeletonModulePromotedProducts';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageHome: FC = () => (
    <Webline>
        <SkeletonModuleBanners className="mb-14" />
        <Skeleton className="mb-14 h-32 w-full" />
        <Skeleton className="mb-4 h-8 w-72" />
        <SkeletonModulePromotedCategories className="mb-10" />
        <Skeleton className="mb-4 h-8 w-72" />
        <SkeletonModulePromotedProducts className="mb-10" />
        <SkeletonModuleMagazine />
    </Webline>
);
