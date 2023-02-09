import { useBlogCategoriesApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const useBlogUrl = (): string | undefined => {
    const [{ data, error }] = useBlogCategoriesApi();
    useQueryError(error);

    return data?.blogCategories.at(0)?.link;
};
