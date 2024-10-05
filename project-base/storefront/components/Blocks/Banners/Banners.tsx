import { BannersSlider } from './BannersSlider';
import { SkeletonModuleBanners } from 'components/Blocks/Skeleton/SkeletonModuleBanners';
import { Webline } from 'components/Layout/Webline/Webline';
import { useSliderItemsQuery } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.generated';

export const Banners: FC = () => {
    const [{ data: sliderItemsData, fetching: areSliderItemsFetching }] = useSliderItemsQuery();

    const weblineTwClasses = 'mb-14 xl:max-w-[1432px]';

    if (areSliderItemsFetching) {
        return (
            <Webline className={weblineTwClasses}>
                <SkeletonModuleBanners />
            </Webline>
        );
    }

    if (!sliderItemsData?.sliderItems.length) {
        return null;
    }

    return (
        <Webline className={weblineTwClasses}>
            <BannersSlider sliderItems={sliderItemsData.sliderItems} />
        </Webline>
    );
};
