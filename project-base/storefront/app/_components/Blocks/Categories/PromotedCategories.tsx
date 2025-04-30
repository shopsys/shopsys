import { PromotedCategoriesContent } from './PromotedCategoriesContent';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { TypePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { Suspense } from 'react';

export type PromotedCategoriesProps = {
    promotedCategoriesData: TypePromotedCategoriesQuery | null | undefined;
};

export async function PromotedCategories({ promotedCategoriesData }: PromotedCategoriesProps) {
    const t = await getTranslation();

    if (!promotedCategoriesData?.promotedCategories.length) {
        return null;
    }

    return (
        <Suspense
            fallback={
                <section>
                    <SkeletonModulePromotedCategories />
                </section>
            }
        >
            <PromotedCategoriesContent promotedCategoriesData={promotedCategoriesData} />
        </Suspense>
    );
}
