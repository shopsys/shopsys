import { SliderItemImagesWebDefaultFragmentApi, SliderItemsQueryApi, useSliderItemsQueryApi } from 'graphql/generated';
import { ImageApiType } from 'types/image';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
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
            images: mapSliderItemImagesApiData(sliderItem.images),
        };
    });
};

const mapSliderItemImagesApiData = (apiData: SliderItemImagesWebDefaultFragmentApi['images']): ImageApiType[] => {
    if (!(0 in apiData) || !(0 in apiData[0].sizes)) {
        return [];
    }

    const mappedImageSizes = mapImageSizeApiData(apiData[0].sizes[0]);
    if (mappedImageSizes === null) {
        return [];
    }

    return [
        {
            ...apiData[0],
            sizes: [mappedImageSizes],
        },
    ];
};
