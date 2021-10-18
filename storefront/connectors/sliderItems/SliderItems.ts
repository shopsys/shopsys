import { ImagesWebDefaultFragmentApi, SliderItemsQueryApi, useSliderItemsQueryApi } from 'graphql/generated';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { ImageApiType } from 'components/Basic/Image/types';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { SliderItem } from './types';
import { useEffect } from 'react';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const getSliderItems = (): SliderItem[] | undefined => {
    const t = useTypedTranslationFunction();
    const [{ data, fetching, error }] = useSliderItemsQueryApi();

    useEffect(() => {
        if (error === undefined) {
            return;
        }

        const parsedErrors = getUserFriendlyErrors(error, t);
        if (parsedErrors.applicationError === undefined) {
            return;
        }

        showErrorMessage(parsedErrors.applicationError);
    }, [fetching]);

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

const mapSliderItemImagesApiData = (apiData: ImagesWebDefaultFragmentApi['images']): ImageApiType[] => {
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
