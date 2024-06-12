import { BannersSlider } from './BannersSlider';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { useSliderItemsQuery } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';

export const Banners: FC = () => {
    const [{ data: sliderItemsData, fetching: areSliderItemsFetching }] = useSliderItemsQuery();

    if (areSliderItemsFetching) {
        return <SkeletonModuleBanners />;
    }

    if (!sliderItemsData?.sliderItems.length) {
        return null;
    }

    return <BannersSlider sliderItems={sliderItemsData.sliderItems} />;
};
