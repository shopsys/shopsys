import { usePromotedCategoriesQueryApi } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';
import { SimpleNavigation } from '../SimpleNavigation/SimpleNavigation';
export const PromotedCategories: FC = () => {
    const [{ data: promotedCategoriesData }] = usePromotedCategoriesQueryApi();

    if (promotedCategoriesData === undefined) {
        return null;
    }

    return <SimpleNavigation listedItems={promotedCategoriesData.promotedCategories} />;
};
