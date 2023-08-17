import * as Types from '../../types';

import gql from 'graphql-tag';
import { BlogArticleConnectionFragmentApi } from '../../articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type BlogCategoryArticlesVariablesApi = Types.Exact<{
    uuid: Types.Scalars['Uuid']['input'];
    endCursor: Types.Scalars['String']['input'];
    pageSize: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;

export type BlogCategoryArticlesApi = {
    __typename?: 'Query';
    blogCategory: {
        __typename?: 'BlogCategory';
        blogArticles: {
            __typename: 'BlogArticleConnection';
            totalCount: number;
            pageInfo: {
                __typename: 'PageInfo';
                hasNextPage: boolean;
                hasPreviousPage: boolean;
                endCursor: string | null;
            };
            edges: Array<{
                __typename: 'BlogArticleEdge';
                node: {
                    __typename: 'BlogArticle';
                    uuid: string;
                    name: string;
                    link: string;
                    publishDate: any;
                    perex: string | null;
                    slug: string;
                    blogCategories: Array<{
                        __typename: 'BlogCategory';
                        uuid: string;
                        name: string;
                        link: string;
                        parent: { __typename?: 'BlogCategory'; name: string } | null;
                    }>;
                    mainImage: {
                        __typename: 'Image';
                        name: string | null;
                        sizes: Array<{
                            __typename: 'ImageSize';
                            size: string;
                            url: string;
                            width: number | null;
                            height: number | null;
                            additionalSizes: Array<{
                                __typename: 'AdditionalSize';
                                height: number | null;
                                media: string;
                                url: string;
                                width: number | null;
                            }>;
                        }>;
                    } | null;
                } | null;
            } | null> | null;
        };
    } | null;
};

export const BlogCategoryArticlesDocumentApi = gql`
    query BlogCategoryArticles($uuid: Uuid!, $endCursor: String!, $pageSize: Int) {
        blogCategory(uuid: $uuid) {
            blogArticles(after: $endCursor, first: $pageSize) {
                ...BlogArticleConnectionFragment
            }
        }
    }
    ${BlogArticleConnectionFragmentApi}
`;

export function useBlogCategoryArticlesApi(
    options: Omit<Urql.UseQueryArgs<BlogCategoryArticlesVariablesApi>, 'query'>,
) {
    return Urql.useQuery<BlogCategoryArticlesApi, BlogCategoryArticlesVariablesApi>({
        query: BlogCategoryArticlesDocumentApi,
        ...options,
    });
}

export interface PossibleTypesResultData {
    possibleTypes: {
        [key: string]: string[];
    };
}
const result: PossibleTypesResultData = {
    possibleTypes: {
        Advert: ['AdvertCode', 'AdvertImage'],
        ArticleInterface: ['ArticleSite', 'BlogArticle'],
        Breadcrumb: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
        CartInterface: ['Cart'],
        CustomerUser: ['CompanyCustomerUser', 'RegularCustomerUser'],
        NotBlogArticleInterface: ['ArticleLink', 'ArticleSite'],
        ParameterFilterOptionInterface: [
            'ParameterCheckboxFilterOption',
            'ParameterColorFilterOption',
            'ParameterSliderFilterOption',
        ],
        PriceInterface: ['Price', 'ProductPrice'],
        Product: ['MainVariant', 'RegularProduct', 'Variant'],
        ProductListable: ['Brand', 'Category', 'Flag'],
        Slug: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
    },
};
export default result;
