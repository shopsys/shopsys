import { BlogPreviewContent } from './BlogPreviewContent';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { Suspense } from 'react';

export const BlogPreview = async () => {
    return (
        <Suspense
            fallback={
                <section className="xxl:-mx-[100px] relative -mx-5">
                    <SkeletonModuleMagazine />
                </section>
            }
        >
            <BlogPreviewContent />
        </Suspense>
    );
};
