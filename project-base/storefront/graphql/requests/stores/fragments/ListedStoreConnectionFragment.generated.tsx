import * as Types from '../../types';

import gql from 'graphql-tag';
import { ListedStoreFragmentApi } from './ListedStoreFragment.generated';
export type ListedStoreConnectionFragmentApi = {
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

export const ListedStoreConnectionFragmentApi = gql`
    fragment ListedStoreConnectionFragment on StoreConnection {
        __typename
        edges {
            __typename
            node {
                ...ListedStoreFragment
            }
        }
    }
    ${ListedStoreFragmentApi}
`;

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
