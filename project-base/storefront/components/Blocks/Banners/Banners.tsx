import { BannersContent } from './BannersContent';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { Suspense } from 'react';

export function Banners() {
    const weblineTwClasses = 'xxl:-mx-[100px]';

    return (
        <Suspense
            fallback={
                <section className={weblineTwClasses}>
                    <SkeletonModuleBanners />
                </section>
            }
        >
            <section className={weblineTwClasses}>
                <BannersContent />
            </section>
        </Suspense>
    );
}
