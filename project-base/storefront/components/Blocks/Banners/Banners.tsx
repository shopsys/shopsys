import { BannersContent } from './BannersContent';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';

export function Banners() {
    return (
        <Webline width="xxl">
            <Suspense fallback={<SkeletonModuleBanners />}>
                <BannersContent />
            </Suspense>
        </Webline>
    );
}
