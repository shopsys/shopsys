import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { usePromotedCategoriesQueryApi } from 'graphql/generated';

export const PromotedCategories: FC = () => {
    const [{ data: promotedCategoriesData }] = usePromotedCategoriesQueryApi();

    if (promotedCategoriesData === undefined) {
        return null;
    }

    return <SimpleNavigation listedItems={promotedCategoriesData.promotedCategories} linkType="category" />;
};
