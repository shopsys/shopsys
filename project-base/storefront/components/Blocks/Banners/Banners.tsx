import { BannersSlider } from './BannersSlider';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeSliderItemsQuery } from 'graphql/requests/sliderItems/queries/SliderItemsQuery.ssr';

export type BannersProps = {
    sliderItemsData: TypeSliderItemsQuery | null | undefined;
};

export function Banners({ sliderItemsData }: BannersProps) {
    if (!sliderItemsData?.sliderItems.length) {
        return null;
    }

    return (
        <Webline width="xxl">
            <BannersSlider sliderItems={sliderItemsData.sliderItems} />
        </Webline>
    );
}
