import { SkeletonModuleBanners } from './SkeletonModuleBanners';
import { SkeletonModuleMagazine } from './SkeletonModuleMagazine';
import { SkeletonModulePromotedCategories } from './SkeletonModulePromotedCategories';
import { SkeletonModulePromotedProducts } from './SkeletonModulePromotedProducts';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageHome: FC = () => (
    <VerticalStack gap="lg">
        <SkeletonModuleBanners />

        <Webline>
            <Skeleton className="h-32 rounded-xl" />
        </Webline>

        <SkeletonModulePromotedCategories />

        <SkeletonModulePromotedProducts />

        <SkeletonModuleMagazine />
    </VerticalStack>
);
