import {
    BlogArticleConnectionFragmentApi,
    BlogArticleDetailFragmentApi,
    ListedBlogArticleFragmentApi,
    useBlogArticlesQueryApi,
} from 'graphql/generated';
import { BlogArticleConnectionType, BlogArticleDetailType, ListedBlogArticleType } from 'types/blogArticle';
import { DomainConfigType } from 'utils/Domain/Domain';
import { mapConnectionEdges } from 'connectors/connection/Connection';
import { mapImageApiData } from 'connectors/image/Image';
import { mapListedProductType } from 'connectors/products/Products';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const blogPreviewVariables = { first: 6, onlyHomepageArticles: true };

export const getBlogPreviewArticles = (): ListedBlogArticleType[] => {
    const [{ data, error }] = useBlogArticlesQueryApi({ variables: blogPreviewVariables });

    useQueryError(error);

    if (data?.blogArticles?.edges === undefined || data.blogArticles.edges === null) {
        return [];
    }

    return mapConnectionEdges<ListedBlogArticleFragmentApi, ListedBlogArticleType>(
        data.blogArticles.edges,
        mapListedBlogArticle,
    );
};

export const mapBlogArticleDetail = (
    apiData: BlogArticleDetailFragmentApi,
    currentDomainConfig: DomainConfigType,
): BlogArticleDetailType => {
    return {
        ...apiData,
        __typename: 'BlogArticle',
        image: mapImageApiData([apiData.image]),
        blogArticleProducts: apiData.blogArticleProducts.map((product) =>
            mapListedProductType(product, currentDomainConfig.currencyCode),
        ),
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
        edges: mapConnectionEdges<ListedBlogArticleFragmentApi, ListedBlogArticleType>(
            apiData.edges,
            mapListedBlogArticle,
        ),
    };
};

export const mapListedBlogArticle = (apiData: ListedBlogArticleFragmentApi): ListedBlogArticleType => {
    return {
        ...apiData,
        image: mapImageApiData([apiData.image]),
    };
};
