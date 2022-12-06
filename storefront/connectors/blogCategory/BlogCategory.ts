import { useBlogCategoriesApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const useBlogUrl = (): string | undefined => {
    const [{ data, error }] = useBlogCategoriesApi();
    useQueryError(error);

    if (data?.blogCategories !== undefined) {
        return data.blogCategories[0].link;
    }

    return undefined;
};
