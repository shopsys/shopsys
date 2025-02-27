import { SkeletonModuleBanners } from './SkeletonModuleBanners';
import { SkeletonModuleMagazine } from './SkeletonModuleMagazine';
import { SkeletonModulePromotedCategories } from './SkeletonModulePromotedCategories';
import { SkeletonModulePromotedProducts } from './SkeletonModulePromotedProducts';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageHome: FC = () => (
    <VerticalStack gap="lg">
        <SkeletonModuleBanners />

        <Webline>
            <Skeleton className="h-32 w-full" />
        </Webline>

        <SkeletonModulePromotedCategories />

        <SkeletonModulePromotedProducts />

        <SkeletonModuleMagazine />
    </VerticalStack>
);
