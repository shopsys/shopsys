import { SkeletonBanners } from 'components/Blocks/Skeleton/SkeletonBanners';
import { BannersSlider } from './BannersSlider';
import { useSliderItemsQueryApi } from 'graphql/generated';

const TEST_IDENTIFIER = 'blocks-banners';

export const Banners: FC = () => {
    const [{ data: sliderItemsData, fetching }] = useSliderItemsQueryApi();

    if (fetching) {
        return <SkeletonBanners />;
    }

    if (!sliderItemsData?.sliderItems.length) {
        return null;
    }

    return <BannersSlider dataTestId={TEST_IDENTIFIER} sliderItems={sliderItemsData.sliderItems} />;
};
