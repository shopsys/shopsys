import { mapConnectionEdges } from 'connectors/connection/Connection';
import {
    BlogArticleConnectionFragmentApi,
    ListedBlogArticleFragmentApi,
    useBlogArticlesQueryApi,
} from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { BlogArticleConnectionType, ListedBlogArticleType } from 'types/blogArticle';

export const blogPreviewVariables = { first: 6, onlyHomepageArticles: true };

export const useBlogPreviewArticles = (): ListedBlogArticleType[] => {
    const [{ data, error }] = useBlogArticlesQueryApi({ variables: blogPreviewVariables });

    useQueryError(error);

    if (data?.blogArticles.edges === undefined || data.blogArticles.edges === null) {
        return [];
    }

    return mapConnectionEdges<ListedBlogArticleFragmentApi, ListedBlogArticleType>(data.blogArticles.edges);
};

export const mapBlogArticleConnection = (
    apiData: BlogArticleConnectionFragmentApi | null,
): BlogArticleConnectionType | null => {
    if (apiData === null) {
        return null;
    }

    return {
        ...apiData,
        edges: mapConnectionEdges<ListedBlogArticleFragmentApi, ListedBlogArticleType>(apiData.edges),
    };
};
