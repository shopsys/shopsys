import { PromotedCategoriesContent } from './PromotedCategoriesContent';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { Suspense } from 'react';

export type PromotedCategoriesProps = {
    promotedCategoriesData: TypePromotedCategoriesQuery | null | undefined;
};

export async function PromotedCategories({ promotedCategoriesData }: PromotedCategoriesProps) {
    if (!promotedCategoriesData?.promotedCategories.length) {
        return null;
    }

    return (
        <Webline>
            <Suspense fallback={<SkeletonModulePromotedCategories />}>
                <PromotedCategoriesContent promotedCategoriesData={promotedCategoriesData} />
            </Suspense>
        </Webline>
    );
}
