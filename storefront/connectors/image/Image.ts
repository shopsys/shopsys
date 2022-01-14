import {
    BlogArticleImageListFragmentApi,
    BlogCategoryImageListFragmentApi,
    BrandDetailFragmentApi,
    BrandImagesListFragmentApi,
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
        | BrandImagesListFragmentApi['images']
        | CategoryImagesDefaultFragmentApi['images']
        | BrandDetailFragmentApi['brandImages']
        | BlogCategoryImageListFragmentApi['image'][]
        | OrderDetailFragmentApi['transport']['images'],
): ImageType | null => {
    if (!(0 in apiData) || apiData[0] === null || apiData[0] === undefined || !(0 in apiData[0].sizes)) {
        return null;
    }

    return mapImageSizeApiData(apiData[0].sizes[0]);
};
