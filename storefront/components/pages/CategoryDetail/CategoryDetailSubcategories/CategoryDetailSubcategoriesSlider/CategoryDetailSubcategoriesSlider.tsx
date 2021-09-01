import 'keen-slider/keen-slider.min.css';
import { CategoryDetailSubcategoriesItemStyled } from '../CategoryDetailSubcategories.style';
import CategoryItem from 'components/blocks/categories/CategoryItem';
import { CategoryItemType } from 'components/blocks/categories/CategoryItem/types';
import { FC } from 'react';
import { theme } from 'theme/main';
import { useKeenSlider } from 'keen-slider/react';

type CategoryDetailSubcategoriesSliderProps = {
    categories: CategoryItemType[];
};

const CategoryDetailSubcategoriesSlider: FC<CategoryDetailSubcategoriesSliderProps> = (props) => {
    const [sliderRef] = useKeenSlider<HTMLDivElement>({
        breakpoints: {
            [theme.mediaQueries.queryTablet]: {
                slidesPerView: 5.3,
                spacing: 10,
            },
            [theme.mediaQueries.queryMobile]: {
                slidesPerView: 4.3,
                spacing: 10,
            },
            [theme.mediaQueries.queryMobileXs]: {
                slidesPerView: 2.5,
                spacing: 10,
            },
        },
    });

    return (
        <div ref={sliderRef} className="keen-slider">
            {props.categories.map((category, key) => (
                <CategoryDetailSubcategoriesItemStyled key={key} className="keen-slider__slide">
                    <CategoryItem category={category}>{category.name}</CategoryItem>
                </CategoryDetailSubcategoriesItemStyled>
            ))}
        </div>
    );
};

export default CategoryDetailSubcategoriesSlider;
