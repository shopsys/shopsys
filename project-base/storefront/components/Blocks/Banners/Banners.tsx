import { useSliderItemsQueryApi } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';
import { BannersSlider } from './BannersSlider';
const TEST_IDENTIFIER = 'blocks-banners';

export const Banners: FC = () => {
    const [{ data: sliderItemsData }] = useSliderItemsQueryApi();

    if (sliderItemsData === undefined || sliderItemsData.sliderItems.length === 0) {
        return null;
    }

    return <BannersSlider sliderItems={sliderItemsData.sliderItems} dataTestId={TEST_IDENTIFIER} />;
};
