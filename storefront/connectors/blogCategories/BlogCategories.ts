import { useBlogCategoriesApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const getBlogCategoriesItems = (): BlogCategoryItem[] | undefined => {
    const [{ data, error }] = useBlogCategoriesApi();
    useQueryError(error);

    return data?.blogCategories;
};

export type BlogCategoryItem = {
    uuid: string;
    name: string;
    link: string;
    children: {
        uuid: string;
        name: string;
        link: string;
    }[];
};
