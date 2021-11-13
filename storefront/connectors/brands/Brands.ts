import { BrandImagesListFragmentApi, ListedBrandFragmentApi } from 'graphql/generated';
import { ImageType } from 'components/Basic/Image/types';
import { ListedBrandType } from './types';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';

export const mapListedBrandApiData = (apiData: ListedBrandFragmentApi): ListedBrandType => {
    return { ...apiData, image: mapBrandsImageApiData(apiData.images) };
};

const mapBrandsImageApiData = (apiData: BrandImagesListFragmentApi['images']): ImageType | null => {
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
