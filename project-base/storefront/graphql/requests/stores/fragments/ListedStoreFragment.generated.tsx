import * as Types from '../../types';

import gql from 'graphql-tag';
import { OpeningHoursFragmentApi } from './OpeningHoursFragment.generated';
import { CountryFragmentApi } from '../../countries/fragments/CountryFragment.generated';
export type ListedStoreFragmentApi = {
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
};

export const ListedStoreFragmentApi = gql`
    fragment ListedStoreFragment on Store {
        __typename
        slug
        identifier: uuid
        name
        description
        openingHours {
            ...OpeningHoursFragment
        }
        locationLatitude
        locationLongitude
        street
        postcode
        city
        country {
            ...CountryFragment
        }
    }
    ${OpeningHoursFragmentApi}
    ${CountryFragmentApi}
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
