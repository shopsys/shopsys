'use client';

import { SkeletonModuleBanners } from './SkeletonModuleBanners';
import { SkeletonModuleMagazine } from './SkeletonModuleMagazine';
import { SkeletonModulePromotedCategories } from './SkeletonModulePromotedCategories';
import { SkeletonModulePromotedProducts } from './SkeletonModulePromotedProducts';
import { Container } from 'app/_components/Layout/Container/Container';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageHome: FC = () => (
    <Container gap="large">
        <SkeletonModuleBanners />

        <Skeleton className="h-56 w-full" />

        <div className="flex flex-col gap-3">
            <Skeleton className="h-8" containerClassName="w-72" />
            <SkeletonModulePromotedCategories />
        </div>

        <div className="flex flex-col gap-3">
            <Skeleton className="h-8" containerClassName="w-72" />
            <SkeletonModulePromotedProducts />
        </div>

        <SkeletonModuleMagazine />
    </Container>
);
