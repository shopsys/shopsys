import { PromotedCategoriesContent } from './PromotedCategoriesContent';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';

export async function PromotedCategories() {
    return (
        <Suspense
            fallback={
                <Webline>
                    <SkeletonModulePromotedCategories />
                </Webline>
            }
        >
            <PromotedCategoriesContent />
        </Suspense>
    );
}
