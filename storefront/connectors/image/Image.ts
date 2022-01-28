import {
    BlogArticleImageListFragmentApi,
    BrandDetailFragmentApi,
    BrandImageDefaultFragmentApi,
    CategoryImagesDefaultFragmentApi,
    OrderDetailFragmentApi,
    ProductImagesListFragmentApi,
} from 'graphql/generated';
import { ImageType } from 'types/image';
import { mapImageSizeApiData } from './size/ImageSize';

export const mapImageApiData = (
    apiData:
        | BlogArticleImageListFragmentApi['image'][]
        | ProductImagesListFragmentApi['images']
        | BrandImageDefaultFragmentApi['images']
        | CategoryImagesDefaultFragmentApi['images']
        | BrandDetailFragmentApi['brandImages']
        | OrderDetailFragmentApi['transport']['images'],
): ImageType | null => {
    if (!(0 in apiData) || apiData[0] === null || !(0 in apiData[0].sizes)) {
        return null;
    }

    return mapImageSizeApiData(apiData[0].sizes[0]);
};
