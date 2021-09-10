import BannersSlider from './BannersSlider';
import { FC } from 'react';
import { getSliderItems } from 'connectors/sliderItems/SliderItems';

/**
 * A component used for displaying propagation banners on home page
 */
const Banners: FC = () => {
    const sliderItems = getSliderItems();

    if (sliderItems === undefined || (Array.isArray(sliderItems) && sliderItems.length === 0)) {
        return null;
    }

    return <BannersSlider sliderItems={sliderItems}></BannersSlider>;
};

/* @component */
export default Banners;
