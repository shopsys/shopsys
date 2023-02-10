import { mapConnectionEdges } from 'connectors/connection/Connection';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import { mapListedProductType } from 'connectors/products/Products';
import {
    BlogArticleConnectionFragmentApi,
    BlogArticleDetailFragmentApi,
    ListedBlogArticleFragmentApi,
    useBlogArticlesQueryApi,
} from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { BlogArticleConnectionType, BlogArticleDetailType, ListedBlogArticleType } from 'types/blogArticle';

export const blogPreviewVariables = { first: 6, onlyHomepageArticles: true };

export const useBlogPreviewArticles = (): ListedBlogArticleType[] => {
    const [{ data, error }] = useBlogArticlesQueryApi({ variables: blogPreviewVariables });

    useQueryError(error);

    if (data?.blogArticles.edges === undefined || data.blogArticles.edges === null) {
        return [];
    }

    return mapConnectionEdges<ListedBlogArticleFragmentApi, ListedBlogArticleType>(data.blogArticles.edges);
};

export const mapBlogArticleDetail = (apiData: BlogArticleDetailFragmentApi): BlogArticleDetailType => {
    return {
        ...apiData,
        __typename: 'BlogArticle',
        blogArticleProducts: apiData.blogArticleProducts.map((product) => mapListedProductType(product)),
    };
};

export const mapBlogArticleConnection = (
    apiData: BlogArticleConnectionFragmentApi | null,
): BlogArticleConnectionType | null => {
    if (apiData === null) {
        return null;
    }

    return {
        ...apiData,
        pageInfo: mapPageInfoApiData(apiData.pageInfo),
        edges: mapConnectionEdges<ListedBlogArticleFragmentApi, ListedBlogArticleType>(apiData.edges),
    };
};
