import { ImageSizesFragmentApi } from 'graphql/generated';
import { ImageType } from 'components/Basic/Image/types';

export const mapImageSizeApiData = (apiData: ImageSizesFragmentApi['sizes'][number]): ImageType | null => {
    if (apiData.width === undefined || apiData.width === null || apiData.height === undefined || apiData.height === null) {
        return null;
    }

    return {
        size: apiData.size,
        url: apiData.url,
        width: apiData.width,
        height: apiData.height,
    };
};
