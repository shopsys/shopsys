import { AdvertsFragmentApi, useAdvertsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const useAdverts = (): AdvertsFragmentApi[] | undefined => {
    const [{ data, error }] = useAdvertsQueryApi();
    useQueryError(error);

    return data?.adverts;
};
