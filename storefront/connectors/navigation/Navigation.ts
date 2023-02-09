import { useNavigationQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { NavigationItem } from 'types/navigation';

export function useNavigationItems(): NavigationItem[] {
    const [{ data, error }] = useNavigationQueryApi();
    useQueryError(error);

    if (data?.navigation !== undefined) {
        return data.navigation;
    }
    return [];
}
