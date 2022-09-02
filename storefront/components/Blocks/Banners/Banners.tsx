import BannersSlider from './BannersSlider';
import { useSliderItems } from 'connectors/sliderItems/SliderItems';
import { FC } from 'react';

const Banners: FC = () => {
    const testIdentifier = 'blocks-banners';

    const sliderItems = useSliderItems();

    if (sliderItems === undefined || (Array.isArray(sliderItems) && sliderItems.length === 0)) {
        return null;
    }

    return <BannersSlider sliderItems={sliderItems} data-testid={testIdentifier}></BannersSlider>;
};

export default Banners;
