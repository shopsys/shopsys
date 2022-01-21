import { ImageSizeFragmentApi, ImageSizesFragmentApi } from 'graphql/generated';
import { ImageSizesType, ImageSizeType } from 'types/image';

export const mapImageSizeTypeApiData = (apiData: ImageSizeFragmentApi): ImageSizeType | null => {
    if (apiData.width === null || apiData.height === null) {
        return null;
    }

    return {
        size: apiData.size,
        url: apiData.url,
        width: apiData.width,
        height: apiData.height,
    };
};

export const getFirstImageSize = (apiData: ImageSizesFragmentApi[]): ImageSizeType | null => {
    if (!(0 in apiData) || !(0 in apiData[0].sizes)) {
        return null;
    }

    return mapImageSizeTypeApiData(apiData[0].sizes[0]);
};

export const mapImageSizesTypeApiData = (images: ImageSizesFragmentApi[]): ImageSizesType[] => {
    const mappedImages = [];
    for (const image of images) {
        const mappedImage: ImageSizesType = {};
        for (const imageSize of image.sizes) {
            mappedImage[imageSize.size] = {
                ...imageSize,
                width: imageSize.width !== null ? imageSize.width : 0,
                height: imageSize.height !== null ? imageSize.height : 0,
            };
        }
        mappedImages.push(mappedImage);
    }

    return mappedImages;
};
