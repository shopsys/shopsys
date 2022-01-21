import { SliderItemsQueryApi, useSliderItemsQueryApi } from 'graphql/generated';
import { getFirstImageSize } from 'connectors/image/Image';
import { SliderItem } from 'types/sliderItem';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const getSliderItems = (): SliderItem[] | undefined => {
    const [{ data, error }] = useSliderItemsQueryApi();
    useQueryError(error);

    if (data === undefined) {
        return undefined;
    }

    return mapSliderItemsApiData(data.sliderItems);
};

const mapSliderItemsApiData = (apiData: SliderItemsQueryApi['sliderItems']): SliderItem[] => {
    return apiData.map((sliderItem) => {
        return {
            uuid: sliderItem.uuid,
            name: sliderItem.name,
            link: sliderItem.link,
            extendedText:
                sliderItem.extendedText === undefined || sliderItem.extendedText === null
                    ? ''
                    : sliderItem.extendedText,
            extendedTextLink:
                sliderItem.extendedTextLink === undefined || sliderItem.extendedTextLink === null
                    ? ''
                    : sliderItem.extendedTextLink,
            image: getFirstImageSize(sliderItem.images),
        };
    });
};
