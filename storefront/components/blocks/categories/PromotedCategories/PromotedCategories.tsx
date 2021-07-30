import { getPromotedCategories } from 'connectors/categories/PromotedCategories';
import { ReactElement } from 'react';

export default function PromotedCategories(): ReactElement | null {
    const promotedCategories = getPromotedCategories();

    if (promotedCategories !== undefined) {
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
