import * as Types from '../../types';

import gql from 'graphql-tag';
import { SeoPageFragmentApi } from '../fragments/SeoPageFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type SeoPageQueryVariablesApi = Types.Exact<{
    pageSlug: Types.Scalars['String']['input'];
}>;

export type SeoPageQueryApi = {
    __typename?: 'Query';
    seoPage: {
        __typename: 'SeoPage';
        title: string | null;
        metaDescription: string | null;
        canonicalUrl: string | null;
        ogTitle: string | null;
        ogDescription: string | null;
        ogImage: {
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

export const SeoPageQueryDocumentApi = gql`
    query SeoPageQuery($pageSlug: String!) {
        seoPage(pageSlug: $pageSlug) {
            ...SeoPageFragment
        }
    }
    ${SeoPageFragmentApi}
`;

export function useSeoPageQueryApi(options: Omit<Urql.UseQueryArgs<SeoPageQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<SeoPageQueryApi, SeoPageQueryVariablesApi>({ query: SeoPageQueryDocumentApi, ...options });
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
