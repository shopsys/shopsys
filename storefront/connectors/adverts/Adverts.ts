import { mapSimpleCategories } from 'connectors/categories/Categories';
import { getFirstImage } from 'connectors/image/Image';
import { AdvertsFragmentApi, useAdvertsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { AdvertType } from 'types/advert';

export const useAdverts = (): AdvertType[] | undefined => {
    const [{ data, error }] = useAdvertsQueryApi();
    useQueryError(error);

    if (data?.adverts === undefined) {
        return undefined;
    }

    return mapAdverts(data.adverts);
};

const mapAdverts = (apiData: AdvertsFragmentApi[]): AdvertType[] => {
    const mappedAdverts = [];

    for (const advertItem of apiData) {
        mappedAdverts.push({
            ...(advertItem.__typename === 'AdvertImage'
                ? {
                      ...advertItem,
                      link: advertItem.link !== null ? advertItem.link : undefined,
                      image: getFirstImage(advertItem.image),
                      imageMobile: getFirstImage(advertItem.imageMobile),
                  }
                : { ...advertItem }),
            categories: mapSimpleCategories(advertItem.categories),
        });
    }

    return mappedAdverts;
};
