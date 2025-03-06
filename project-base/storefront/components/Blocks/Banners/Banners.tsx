import { BannersContent } from './BannersContent';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';

export function Banners() {
    return (
        <Suspense
            fallback={
                <Webline className="mb-14 xl:max-w-[1432px]">
                    <SkeletonModuleBanners />
                </Webline>
            }
        >
            <Webline className="mb-14 xl:max-w-[1432px]">
                <BannersContent />
            </Webline>
        </Suspense>
    );
}
