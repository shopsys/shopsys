import { CategoryDetailAdvancedSeoCategoriesItemStyled as AdvancedSeoCategoriesItemStyled } from 'components/Pages/CategoryDetail/CategoryDetailAdvancedSeoCategories/CategoryDetailAdvancedSeoCategories.style';
import { theme } from 'components/Theme/main';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';
import NextLink from 'next/link';
import { FC } from 'react';
import { ReadyCategorySeoMixLink } from 'types/category';

type CategoryDetailAdvancedSeoCategoriesSliderProps = {
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
};

const CategoryDetailAdvancedSeoCategoriesSlider: FC<CategoryDetailAdvancedSeoCategoriesSliderProps> = (props) => {
    const [sliderRef] = useKeenSlider<HTMLDivElement>({
        breakpoints: {
            [theme.mediaQueries.queryTablet]: {
                slidesPerView: 2.2,
                spacing: 15,
            },
            [theme.mediaQueries.queryMobile]: {
                slidesPerView: 1.2,
                spacing: 15,
            },
        },
    });

    return (
        <div ref={sliderRef} className="keen-slider">
            {props.readyCategorySeoMixLinks.map((seoMixLink, index) => (
                <NextLink key={index} href={seoMixLink.slug} passHref>
                    <AdvancedSeoCategoriesItemStyled className="keen-slider__slide">
                        {seoMixLink.name}
                    </AdvancedSeoCategoriesItemStyled>
                </NextLink>
            ))}
        </div>
    );
};

export default CategoryDetailAdvancedSeoCategoriesSlider;
