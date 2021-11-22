import { FC } from 'react';
import { getPromotedCategories } from 'connectors/categories/PromotedCategories';
import { PromotedCategoriesSimpleNavigationStyled } from './PromotedCategories.style';

const PromotedCategories: FC = () => {
    const promotedCategories = getPromotedCategories();

    if (promotedCategories !== undefined) {
        return <PromotedCategoriesSimpleNavigationStyled listedItems={promotedCategories} />;
    }

    return null;
};

export default PromotedCategories;
