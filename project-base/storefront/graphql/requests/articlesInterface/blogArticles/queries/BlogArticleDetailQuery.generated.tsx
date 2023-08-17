import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogArticleDetailFragmentApi } from '../fragments/BlogArticleDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type BlogArticleDetailQueryVariablesApi = Types.Exact<{
    urlSlug: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;

export type BlogArticleDetailQueryApi = {
    __typename?: 'Query';
    blogArticle: {
        __typename: 'BlogArticle';
        id: number;
        uuid: string;
        name: string;
        slug: string;
        link: string;
        text: string | null;
        publishDate: any;
        seoTitle: string | null;
        seoMetaDescription: string | null;
        seoH1: string | null;
        breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
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
};

export const BlogArticleDetailQueryDocumentApi = gql`
    query BlogArticleDetailQuery($urlSlug: String) {
        blogArticle(urlSlug: $urlSlug) {
            ...BlogArticleDetailFragment
        }
    }
    ${BlogArticleDetailFragmentApi}
`;

export function useBlogArticleDetailQueryApi(
    options?: Omit<Urql.UseQueryArgs<BlogArticleDetailQueryVariablesApi>, 'query'>,
) {
    return Urql.useQuery<BlogArticleDetailQueryApi, BlogArticleDetailQueryVariablesApi>({
        query: BlogArticleDetailQueryDocumentApi,
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
