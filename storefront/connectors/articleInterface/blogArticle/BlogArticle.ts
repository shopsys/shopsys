import { mapConnectionEdges } from 'connectors/connection/Connection';
import { getFirstImage } from 'connectors/image/Image';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import { mapListedProductType } from 'connectors/products/Products';
import {
    BlogArticleConnectionFragmentApi,
    BlogArticleDetailFragmentApi,
    ListedBlogArticleFragmentApi,
    SimpleBlogArticleFragmentApi,
    useBlogArticlesQueryApi,
} from 'graphql/generated';
import { DomainConfigType } from 'helpers/domain/domain';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import {
    BlogArticleConnectionType,
    BlogArticleDetailType,
    ListedBlogArticleType,
    SimpleBlogArticleType,
} from 'types/blogArticle';

export const blogPreviewVariables = { first: 6, onlyHomepageArticles: true };

export const useBlogPreviewArticles = (): ListedBlogArticleType[] => {
    const [{ data, error }] = useBlogArticlesQueryApi({ variables: blogPreviewVariables });

    useQueryError(error);

    if (data?.blogArticles.edges === undefined || data.blogArticles.edges === null) {
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
        image: getFirstImage(apiData.blogArticlesGridImages),
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
        image: getFirstImage(apiData.images),
    };
};

export const mapSimpleBlogArticle = (apiData: SimpleBlogArticleFragmentApi): SimpleBlogArticleType => {
    return {
        ...apiData,
        image: getFirstImage(apiData.images),
    };
};
