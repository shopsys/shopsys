import {
    CategoryDetailSubcategoriesItemStyled,
    CategoryDetailSubcategoriesListStyled,
    CategoryDetailSubcategoriesWrapperStyled,
} from './CategoryDetailSubcategories.style';
import { FC, useState } from 'react';
import CategoryDetailSubcategoriesSlider from './CategoryDetailSubcategoriesSlider';
import CategoryItem from 'components/Blocks/Categories/CategoryItem';
import { CategoryItemType } from 'components/Blocks/Categories/CategoryItem/types';
import { desktopFirstSizes } from '../../../Theme/mediaQueries';
import { getIsElementVisible } from 'components/Helpers/GetIsItemVisible';
import { useGetWindowSize } from 'hooks/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/UseResizeWidthEffect';

type CategoryDetailSubcategoriesProps = {
    categories: CategoryItemType[];
};

export const CategoryDetailSubcategories: FC<CategoryDetailSubcategoriesProps> = (props) => {
    const { width } = useGetWindowSize();
    const [isSubcategoriesSliderVisible, setSubcategoriesSliderVisible] = useState(true);
    useResizeWidthEffect(
        width,
        desktopFirstSizes.tablet,
        () => setSubcategoriesSliderVisible(false),
        () => setSubcategoriesSliderVisible(true),
        () => setSubcategoriesSliderVisible(getIsElementVisible([{ min: 0, max: 768 }], width)),
    );

    return (
        <CategoryDetailSubcategoriesWrapperStyled>
            {isSubcategoriesSliderVisible ? (
                <CategoryDetailSubcategoriesSlider categories={props.categories} />
            ) : (
                <CategoryDetailSubcategoriesListStyled>
                    {props.categories.map((category, key) => (
                        <CategoryDetailSubcategoriesItemStyled key={key}>
                            <CategoryItem category={category}>{category.name}</CategoryItem>
                        </CategoryDetailSubcategoriesItemStyled>
                    ))}
                </CategoryDetailSubcategoriesListStyled>
            )}
        </CategoryDetailSubcategoriesWrapperStyled>
    );
};

export default CategoryDetailSubcategories;
