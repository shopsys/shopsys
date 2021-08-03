import { FC } from 'react';
import { getPromotedCategories } from '../../../../connectors/categories/PromotedCategories';

const PromotedCategories: FC = () => {
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
};

export default PromotedCategories;
