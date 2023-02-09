import { CategoriesByColumnFragmentApi, useNavigationQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export function useNavigationItems(): CategoriesByColumnFragmentApi[] | undefined {
    const [{ data, error }] = useNavigationQueryApi();
    useQueryError(error);

    return data?.navigation;
}
