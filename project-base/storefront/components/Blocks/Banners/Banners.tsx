import { BannersContent } from './BannersContent';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { Suspense } from 'react';

export function Banners() {
    const weblineTwClasses = 'xxl:-mx-[100px]';

    return (
        <Suspense
            fallback={
                <Webline width="xxl">
                    <SkeletonModuleBanners />
                </section>
            }
        >
            <Webline width="xxl">
                <BannersContent />
            </section>
        </Suspense>
    );
}
