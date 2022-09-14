import { BannersSlider } from './BannersSlider/BannersSlider';
import { useSliderItems } from 'connectors/sliderItems/SliderItems';
import { FC } from 'react';

const TEST_IDENTIFIER = 'blocks-banners';

export const Banners: FC = () => {
    const sliderItems = useSliderItems();

    if (sliderItems === undefined || (Array.isArray(sliderItems) && sliderItems.length === 0)) {
        return null;
    }

    return <BannersSlider sliderItems={sliderItems} testIdentifier={TEST_IDENTIFIER} />;
};
