import { SliderItemFragmentApi, useSliderItemsQueryApi } from 'graphql/generated';
import { getFirstImage } from 'connectors/image/Image';
import { SliderItemType } from 'types/sliderItem';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const useSliderItems = (): SliderItemType[] | undefined => {
    const [{ data, error }] = useSliderItemsQueryApi();
    useQueryError(error);

    if (data === undefined) {
        return undefined;
    }

    return mapSliderItemsApiData(data.sliderItems);
};

const mapSliderItemApiData = (apiData: SliderItemFragmentApi): SliderItemType => {
    return {
        ...apiData,
        extendedText: apiData.extendedText === null ? '' : apiData.extendedText,
        extendedTextLink: apiData.extendedTextLink === null ? '' : apiData.extendedTextLink,
        webImages: getFirstImage(apiData.webImages),
        mobileImages: getFirstImage(apiData.mobileImages),
    };
};

const mapSliderItemsApiData = (apiData: SliderItemFragmentApi[]): SliderItemType[] => {
    return apiData.map((sliderItem) => mapSliderItemApiData(sliderItem));
};
