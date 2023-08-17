import * as Types from '../../types';

import gql from 'graphql-tag';
import { ListedStoreConnectionFragmentApi } from '../fragments/ListedStoreConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type StoresQueryVariablesApi = Types.Exact<{ [key: string]: never }>;

export type StoresQueryApi = {
    __typename?: 'Query';
    stores: {
        __typename: 'StoreConnection';
        edges: Array<{
            __typename: 'StoreEdge';
            node: {
                __typename: 'Store';
                slug: string;
                name: string;
                description: string | null;
                locationLatitude: string | null;
                locationLongitude: string | null;
                street: string;
                postcode: string;
                city: string;
                identifier: string;
                openingHours: {
                    __typename?: 'OpeningHours';
                    isOpen: boolean;
                    dayOfWeek: number;
                    openingHoursOfDays: Array<{
                        __typename?: 'OpeningHoursOfDay';
                        dayOfWeek: number;
                        firstOpeningTime: string | null;
                        firstClosingTime: string | null;
                        secondOpeningTime: string | null;
                        secondClosingTime: string | null;
                    }>;
                };
                country: { __typename: 'Country'; name: string; code: string };
            } | null;
        } | null> | null;
    };
};

export const StoresQueryDocumentApi = gql`
    query StoresQuery {
        stores {
            ...ListedStoreConnectionFragment
        }
    }
    ${ListedStoreConnectionFragmentApi}
`;

export function useStoresQueryApi(options?: Omit<Urql.UseQueryArgs<StoresQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<StoresQueryApi, StoresQueryVariablesApi>({ query: StoresQueryDocumentApi, ...options });
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
