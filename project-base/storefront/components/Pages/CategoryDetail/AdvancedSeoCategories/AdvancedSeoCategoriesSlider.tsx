import { AdvancedSeoCategoriesItem } from './AdvancedSeoCategoriesItem';
import { mediaQueries } from 'components/Theme/mediaQueries';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import 'keen-slider/keen-slider.min.css';
import { useKeenSlider } from 'keen-slider/react';

type AdvancedSeoCategoriesSliderProps = {
    readyCategorySeoMixLinks: CategoryDetailFragmentApi['readyCategorySeoMixLinks'];
};

export const AdvancedSeoCategoriesSlider: FC<AdvancedSeoCategoriesSliderProps> = ({ readyCategorySeoMixLinks }) => {
    const [sliderRef] = useKeenSlider<HTMLDivElement>({
        breakpoints: {
            [mediaQueries.queryTablet]: {
                slidesPerView: 2.2,
                spacing: 15,
            },
            [mediaQueries.queryMobile]: {
                slidesPerView: 1.2,
                spacing: 15,
            },
        },
    });

    return (
        <div ref={sliderRef} className="keen-slider mb-6">
            {readyCategorySeoMixLinks.map((seoMixLink, index) => (
                <AdvancedSeoCategoriesItem key={index} slug={seoMixLink.slug} className="keen-slider__slide">
                    {seoMixLink.name}
                </AdvancedSeoCategoriesItem>
            ))}
        </div>
    );
};
