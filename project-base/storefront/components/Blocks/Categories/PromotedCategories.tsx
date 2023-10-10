import { SkeletonPromotedCategories } from 'components/Blocks/Skeleton/SkeletonPromotedCategories';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { usePromotedCategoriesQueryApi } from 'graphql/generated';

export const PromotedCategories: FC = () => {
    const [{ data: promotedCategoriesData, fetching }] = usePromotedCategoriesQueryApi();

    if (fetching) {
        return <SkeletonPromotedCategories />;
    }

    if (!promotedCategoriesData) {
        return null;
    }

    return <SimpleNavigation linkType="category" listedItems={promotedCategoriesData.promotedCategories} />;
};
