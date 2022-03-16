import { BlogCategoryDetailFragmentApi, useBlogCategoriesApi } from 'graphql/generated';
import { BlogCategoryDetailType, ListedBlogCategoryType } from 'types/blogCategory';
import { mapBlogArticleConnection } from 'connectors/articleInterface/blogArticle/BlogArticle';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const useBlogCategoryItems = (): ListedBlogCategoryType[] | undefined => {
    const [{ data, error }] = useBlogCategoriesApi();

    useQueryError(error);

    return data?.blogCategories;
};

export const useBlogUrl = (): string | undefined => {
    const [{ data, error }] = useBlogCategoriesApi();
    useQueryError(error);

    if (data?.blogCategories !== undefined) {
        return data.blogCategories[0].link;
    }

    return undefined;
};

export const mapBlogCategoryDetail = (apiData: BlogCategoryDetailFragmentApi): BlogCategoryDetailType => {
    return {
        ...apiData,
        __typename: 'BlogCategory',
        blogArticles: mapBlogArticleConnection(apiData.blogArticles),
    };
};
