import { BannersSlider } from './BannersSlider';
import { getSliderItemsQuery } from 'app/_queries/getSliderItemsQuery';

export const BannersContent = async () => {
    const sliderItemsData = await getSliderItemsQuery();

    if (!sliderItemsData?.sliderItems.length) {
        return null;
    }

    return <BannersSlider sliderItems={sliderItemsData.sliderItems} />;
};
