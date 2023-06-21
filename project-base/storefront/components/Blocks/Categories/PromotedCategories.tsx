import { SimpleNavigation } from '../SimpleNavigation/SimpleNavigation';
import { usePromotedCategoriesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const PromotedCategories: FC = () => {
    const [{ data: promotedCategoriesData }] = useQueryError(usePromotedCategoriesQueryApi());

    if (promotedCategoriesData === undefined) {
        return null;
    }

    return <SimpleNavigation listedItems={promotedCategoriesData.promotedCategories} />;
};
