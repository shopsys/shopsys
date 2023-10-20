import { BannersSlider } from './BannersSlider';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { useSliderItemsQueryApi } from 'graphql/generated';

const TEST_IDENTIFIER = 'blocks-banners';

export const Banners: FC = () => {
    const [{ data: sliderItemsData, fetching }] = useSliderItemsQueryApi();

    if (fetching) {
        return <SkeletonModuleBanners />;
    }

    if (!sliderItemsData?.sliderItems.length) {
        return null;
    }

    return <BannersSlider dataTestId={TEST_IDENTIFIER} sliderItems={sliderItemsData.sliderItems} />;
};
