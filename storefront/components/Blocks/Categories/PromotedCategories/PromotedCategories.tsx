import { PromotedCategoryListItemStyled, PromotedCategoryListStyled } from './PromotedCategories.style';
import CategoryItem from 'components/Blocks/Categories/CategoryItem';
import { FC } from 'react';
import { getPromotedCategories } from 'connectors/categories/PromotedCategories';

const PromotedCategories: FC = () => {
    const promotedCategories = getPromotedCategories();

    if (promotedCategories !== undefined) {
        return (
            <PromotedCategoryListStyled>
                {promotedCategories.map((category) => (
                    <PromotedCategoryListItemStyled key={category.uuid.toString()}>
                        <CategoryItem category={category} />
                    </PromotedCategoryListItemStyled>
                ))}
            </PromotedCategoryListStyled>
        );
    }

    return null;
};

export default PromotedCategories;
