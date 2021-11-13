import {
    BlogArticleImageListFragmentApi,
    BrandImagesListFragmentApi,
    ProductImagesListFragmentApi,
} from 'graphql/generated';
import { ImageType } from 'components/Basic/Image/types';
import { mapImageSizeApiData } from './size/ImageSize';

export const mapImageApiData = (
    apiData:
        | BlogArticleImageListFragmentApi['image'][]
        | ProductImagesListFragmentApi['images']
        | BrandImagesListFragmentApi['images']
): ImageType | null => {
    if (
        apiData === null ||
        apiData === undefined ||
        apiData[0] === null ||
        apiData[0] === undefined ||
        !(0 in apiData) ||
        !(0 in apiData[0].sizes)
    ) {
        return null;
    }

    return mapImageSizeApiData(apiData[0].sizes[0]);
};
