import { BlogCategoryItem } from 'types/blogCategory';
import { useBlogCategoriesApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const getBlogCategoriesItems = (): BlogCategoryItem[] | undefined => {
    const [{ data, error }] = useBlogCategoriesApi();
    useQueryError(error);

    return data?.blogCategories;
};

export const getBlogUrl = (): string | undefined => {
    const [{ data, error }] = useBlogCategoriesApi();
    useQueryError(error);

    if (data?.blogCategories !== undefined) {
        return data.blogCategories[0].link;
    }

    return undefined;
};
