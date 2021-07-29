import { mapPromotedCategories, promotedCategoriesQuery } from 'connectors/categories/PromotedCategories';
import { ReactElement } from 'react';
import { useFetchQuery } from 'hooks/UseFetchQuery';

export default function PromotedCategories(): ReactElement | null {
    const result = useFetchQuery({ query: promotedCategoriesQuery });
    const promotedCategories = mapPromotedCategories(result?.data);

    if (promotedCategories.length > 0) {
        return (
            <ul>
                {promotedCategories.map(({ uuid, name }) => (
                    <li key={uuid}>{name}</li>
                ))}
            </ul>
        );
    }

    return null;
}
