import 'keen-slider/keen-slider.min.css';
import { CategoryDetailAdvancedSeoCategoriesItem as AdvancedSeoCategoriesItem } from '../CategoryDetailAdvancedSeoCategories.style';
import { FC } from 'react';
import Link from 'next/link';
import { ReadyCategorySeoMixLink } from '../../types';
import { theme } from 'theme/main';
import { useKeenSlider } from 'keen-slider/react';

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
                <Link key={index} href={seoMixLink.slug} passHref>
                    <AdvancedSeoCategoriesItem className="keen-slider__slide">
                        {seoMixLink.name}
                    </AdvancedSeoCategoriesItem>
                </Link>
            ))}
        </div>
    );
};

export default CategoryDetailAdvancedSeoCategoriesSlider;
