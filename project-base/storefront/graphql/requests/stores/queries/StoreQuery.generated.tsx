import * as Types from '../../types';

import gql from 'graphql-tag';
import { ListedStoreFragmentApi } from '../fragments/ListedStoreFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type StoreQueryVariablesApi = Types.Exact<{
    uuid: Types.InputMaybe<Types.Scalars['Uuid']['input']>;
}>;

export type StoreQueryApi = {
    __typename?: 'Query';
    store: {
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
};

export const StoreQueryDocumentApi = gql`
    query StoreQuery($uuid: Uuid) {
        store(uuid: $uuid) {
            ...ListedStoreFragment
        }
    }
    ${ListedStoreFragmentApi}
`;

export function useStoreQueryApi(options?: Omit<Urql.UseQueryArgs<StoreQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<StoreQueryApi, StoreQueryVariablesApi>({ query: StoreQueryDocumentApi, ...options });
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
