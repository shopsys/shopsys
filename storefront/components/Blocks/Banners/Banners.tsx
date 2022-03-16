import BannersSlider from './BannersSlider';
import { FC } from 'react';
import { useSliderItems } from 'connectors/sliderItems/SliderItems';

/**
 * A component used for displaying propagation banners on home page
 */
const Banners: FC = () => {
    const testIdentifier = 'blocks-banners';

    const sliderItems = useSliderItems();

    if (sliderItems === undefined || (Array.isArray(sliderItems) && sliderItems.length === 0)) {
        return null;
    }

    return <BannersSlider sliderItems={sliderItems} data-testid={testIdentifier}></BannersSlider>;
};

/* @component */
export default Banners;
