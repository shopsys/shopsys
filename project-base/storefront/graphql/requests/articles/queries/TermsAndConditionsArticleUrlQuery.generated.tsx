import * as Types from '../../types';

import gql from 'graphql-tag';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TermsAndConditionsArticleUrlQueryVariablesApi = Types.Exact<{ [key: string]: never }>;

export type TermsAndConditionsArticleUrlQueryApi = {
    __typename?: 'Query';
    termsAndConditionsArticle: { __typename?: 'ArticleSite'; slug: string } | null;
};

export const TermsAndConditionsArticleUrlQueryDocumentApi = gql`
    query TermsAndConditionsArticleUrlQuery {
        termsAndConditionsArticle {
            slug
        }
    }
`;

export function useTermsAndConditionsArticleUrlQueryApi(
    options?: Omit<Urql.UseQueryArgs<TermsAndConditionsArticleUrlQueryVariablesApi>, 'query'>,
) {
    return Urql.useQuery<TermsAndConditionsArticleUrlQueryApi, TermsAndConditionsArticleUrlQueryVariablesApi>({
        query: TermsAndConditionsArticleUrlQueryDocumentApi,
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
