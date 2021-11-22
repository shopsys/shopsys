import { ListedBrandFragmentApi } from 'graphql/generated';
import { ListedBrandType } from './types';
import { mapImageApiData } from 'connectors/image/Image';

export const mapListedBrandApiData = (apiData: ListedBrandFragmentApi): ListedBrandType => {
    return { ...apiData, image: mapImageApiData(apiData.images) };
};
