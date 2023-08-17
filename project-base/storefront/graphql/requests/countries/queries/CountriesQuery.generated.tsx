import * as Types from '../../types';

import gql from 'graphql-tag';
import { CountryFragmentApi } from '../fragments/CountryFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type CountriesQueryVariablesApi = Types.Exact<{ [key: string]: never }>;

export type CountriesQueryApi = {
    __typename?: 'Query';
    countries: Array<{ __typename: 'Country'; name: string; code: string }>;
};

export const CountriesQueryDocumentApi = gql`
    query CountriesQuery {
        countries {
            ...CountryFragment
        }
    }
    ${CountryFragmentApi}
`;

export function useCountriesQueryApi(options?: Omit<Urql.UseQueryArgs<CountriesQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<CountriesQueryApi, CountriesQueryVariablesApi>({
        query: CountriesQueryDocumentApi,
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
