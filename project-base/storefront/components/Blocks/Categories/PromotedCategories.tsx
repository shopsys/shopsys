import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { usePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';

export const PromotedCategories: FC = () => {
    const [{ data: promotedCategoriesData, fetching }] = usePromotedCategoriesQuery();

    if (fetching) {
        return <SkeletonModulePromotedCategories />;
    }

    if (!promotedCategoriesData) {
        return null;
    }

    return <SimpleNavigation listedItems={promotedCategoriesData.promotedCategories} />;
};
