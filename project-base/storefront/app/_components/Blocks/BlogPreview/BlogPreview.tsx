import { BlogPreviewContent } from './BlogPreviewContent';
import { SkeletonModuleMagazine } from 'components/Blocks/Skeleton/SkeletonModuleMagazine';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';

export const BlogPreview = async () => {
    return (
        <Webline width="xxl">
            <Suspense fallback={<SkeletonModuleMagazine />}>
                <BlogPreviewContent />
            </Suspense>
        </Webline>
    );
};
