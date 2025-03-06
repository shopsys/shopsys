import { PromotedCategoriesContent } from './PromotedCategoriesContent';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { Suspense } from 'react';

export async function PromotedCategories() {
    return (
        <Suspense
            fallback={
                <section>
                    <SkeletonModulePromotedCategories />
                </section>
            }
        >
            <PromotedCategoriesContent />
        </Suspense>
    );
}
