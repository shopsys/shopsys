import { BannersSlider } from './BannersSlider';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { Webline } from 'components/Layout/Webline/Webline';
import { useSliderItemsQuery } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';

export const Banners: FC = () => {
    const [{ data: sliderItemsData, fetching: areSliderItemsFetching }] = useSliderItemsQuery();

    if (areSliderItemsFetching) {
        return (
            <Webline width="xxl">
                <SkeletonModuleBanners />
            </Webline>
        );
    }

    if (!sliderItemsData?.sliderItems.length) {
        return null;
    }

    return (
        <Webline width="xxl">
            <BannersSlider sliderItems={sliderItemsData.sliderItems} />
        </Webline>
    );
};
