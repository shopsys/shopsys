import { SimpleNavigation } from '../SimpleNavigation/SimpleNavigation';
import { usePromotedCategoriesQueryApi } from 'graphql/generated';

export const PromotedCategories: FC = () => {
    const [{ data: promotedCategoriesData }] = usePromotedCategoriesQueryApi();

    if (promotedCategoriesData === undefined) {
        return null;
    }

    return <SimpleNavigation listedItems={promotedCategoriesData.promotedCategories} />;
};
