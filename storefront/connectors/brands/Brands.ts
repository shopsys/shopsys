import { ListedBrandFragmentApi, useBrandsQueryApi } from 'graphql/generated';
import { ListedBrandType } from './types';
import { mapImageApiData } from 'connectors/image/Image';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const mapListedBrandApiData = (apiData: ListedBrandFragmentApi): ListedBrandType => {
    return { ...apiData, image: mapImageApiData(apiData.images) };
};

export function getBrands(): ListedBrandType[] | undefined {
    const [{ data, error }] = useBrandsQueryApi();
    useQueryError(error);

    if (data?.brands === undefined) {
        return undefined;
    }

    return data.brands.map((apiBrand) => mapListedBrandApiData(apiBrand));
}
