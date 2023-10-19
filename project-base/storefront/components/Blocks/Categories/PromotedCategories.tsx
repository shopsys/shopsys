import { SkeletonModulePromotedCategories } from 'components/Blocks/Skeleton/SkeletonModulePromotedCategories';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { usePromotedCategoriesQueryApi } from 'graphql/generated';

export const PromotedCategories: FC = () => {
    const [{ data: promotedCategoriesData, fetching }] = usePromotedCategoriesQueryApi();

    if (fetching) {
        return <SkeletonModulePromotedCategories />;
    }

    if (!promotedCategoriesData) {
        return null;
    }

    return <SimpleNavigation linkType="category" listedItems={promotedCategoriesData.promotedCategories} />;
};
