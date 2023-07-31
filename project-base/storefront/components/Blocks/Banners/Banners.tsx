import { BannersSlider } from './BannersSlider';
import { useSliderItemsQueryApi } from 'graphql/generated';

const TEST_IDENTIFIER = 'blocks-banners';

export const Banners: FC = () => {
    const [{ data: sliderItemsData }] = useSliderItemsQueryApi();

    if (sliderItemsData === undefined || sliderItemsData.sliderItems.length === 0) {
        return null;
    }

    return <BannersSlider sliderItems={sliderItemsData.sliderItems} dataTestId={TEST_IDENTIFIER} />;
};
