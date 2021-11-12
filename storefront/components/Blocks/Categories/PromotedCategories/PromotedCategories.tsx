import { FC } from 'react';
import { getPromotedCategories } from 'connectors/categories/PromotedCategories';
import SimpleNavigation from 'components/Blocks/SimpleNavigation';

const PromotedCategories: FC = () => {
    const promotedCategories = getPromotedCategories();

    if (promotedCategories !== undefined) {
        return <SimpleNavigation listedItems={promotedCategories} />;
    }

    return null;
};

export default PromotedCategories;
