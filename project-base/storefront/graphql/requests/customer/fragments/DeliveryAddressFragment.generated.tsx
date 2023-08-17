import * as Types from '../../types';

import gql from 'graphql-tag';
import { CountryFragmentApi } from '../../countries/fragments/CountryFragment.generated';
export type DeliveryAddressFragmentApi = {
    __typename: 'DeliveryAddress';
    uuid: string;
    companyName: string | null;
    street: string | null;
    city: string | null;
    postcode: string | null;
    telephone: string | null;
    firstName: string | null;
    lastName: string | null;
    country: { __typename: 'Country'; name: string; code: string } | null;
};

export const DeliveryAddressFragmentApi = gql`
    fragment DeliveryAddressFragment on DeliveryAddress {
        __typename
        uuid
        companyName
        street
        city
        postcode
        telephone
        country {
            ...CountryFragment
        }
        firstName
        lastName
    }
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
