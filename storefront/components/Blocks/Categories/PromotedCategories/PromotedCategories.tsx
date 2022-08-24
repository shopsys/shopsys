import { PromotedCategoriesSimpleNavigationStyled } from './PromotedCategories.style';
import { usePromotedCategories } from 'connectors/categories/PromotedCategories';
import { FC } from 'react';

export const PromotedCategories: FC = () => {
    const promotedCategories = usePromotedCategories();

    if (promotedCategories !== undefined) {
        return <PromotedCategoriesSimpleNavigationStyled listedItems={promotedCategories} />;
    }

    return null;
};
