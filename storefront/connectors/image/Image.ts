import { ImageSizeFragmentApi, ImageSizesFragmentApi } from 'graphql/generated';
import { ImageSizeType, ImageType } from 'types/image';

export const mapImageSizeTypeApiData = (apiData: ImageSizeFragmentApi): ImageSizeType | null => {
    if (apiData.width === null && apiData.height === null) {
        return null;
    }

    return {
        size: apiData.size,
        url: apiData.url,
        width: apiData.width,
        height: apiData.height,
        additionalSizes: apiData.additionalSizes.map((image) => ({
            height: image.height,
            url: image.url,
            width: image.width,
            media: image.media,
        })),
    };
};

export const getFirstImage = (apiData: ImageSizesFragmentApi[]): ImageType | null => {
    if (!(0 in apiData) || !(0 in apiData[0].sizes)) {
        return null;
    }

    return {
        sizes: apiData[0].sizes
            .map((size) => mapImageSizeTypeApiData(size))
            .filter((i) => i !== null) as ImageSizeType[],
    };
};

export const mapImageSizesTypeApiData = (images: ImageSizesFragmentApi[]): ImageType[] => {
    const mappedImages: ImageType[] = [];
    for (const image of images) {
        mappedImages.push({
            sizes: image.sizes
                .map((size) => mapImageSizeTypeApiData(size))
                .filter((i) => i !== null) as ImageSizeType[],
        });
    }

    return mappedImages;
};
