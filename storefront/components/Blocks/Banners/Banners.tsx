import BannersSlider from './BannersSlider';
import { FC } from 'react';
import { getSliderItems } from 'connectors/sliderItems/SliderItems';

const Banners: FC = () => {
    const sliderItems = getSliderItems();

    if (sliderItems === undefined || (Array.isArray(sliderItems) && sliderItems.length === 0)) {
        return null;
    }

    return <BannersSlider sliderItems={sliderItems}></BannersSlider>;
};

export default Banners;
