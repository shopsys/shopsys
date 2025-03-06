import { BlogPreviewContent } from './BlogPreviewContent';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';

export const BlogPreview = async () => {
    return (
        <Suspense
            fallback={
                <Webline className="relative px-0 xl:max-w-[1400px]">
                    <SkeletonModuleMagazine />
                </Webline>
            }
        >
            <BlogPreviewContent />
        </Suspense>
    );
};
