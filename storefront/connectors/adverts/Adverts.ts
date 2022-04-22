import { AdvertsFragmentApi, useAdvertsQueryApi } from 'graphql/generated';
import { AdvertType } from 'types/advert';
import { getFirstImage } from 'connectors/image/Image';
import { mapSimpleCategories } from 'connectors/categories/Categories';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

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
