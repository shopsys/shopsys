import { StyledPromotedCategoryList, StyledPromotedCategoryListItem } from './PromotedCategories.style';
import CategoryItem from '../CategoryItem';
import { FC } from 'react';
import { getPromotedCategories } from '../../../../connectors/categories/PromotedCategories';

const PromotedCategories: FC = () => {
    const promotedCategories = getPromotedCategories();

    if (promotedCategories !== undefined) {
        return (
            <StyledPromotedCategoryList>
                {promotedCategories.map((category) => (
                    <StyledPromotedCategoryListItem key={category.uuid}>
                        <CategoryItem category={category} />
                    </StyledPromotedCategoryListItem>
                ))}
            </StyledPromotedCategoryList>
        );
    }

    return null;
};

export default PromotedCategories;
