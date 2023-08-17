import * as Types from '../../types';

import gql from 'graphql-tag';
import { StoreDetailFragmentApi } from '../../stores/fragments/StoreDetailFragment.generated';
export type StoreAvailabilityFragmentApi = {
    __typename: 'StoreAvailability';
    exposed: boolean;
    availabilityInformation: string;
    availabilityStatus: Types.AvailabilityStatusEnumApi;
    store: {
        __typename: 'Store';
        uuid: string;
        slug: string;
        description: string | null;
        street: string;
        city: string;
        postcode: string;
        contactInfo: string | null;
        specialMessage: string | null;
        locationLatitude: string | null;
        locationLongitude: string | null;
        storeName: string;
        country: { __typename: 'Country'; name: string; code: string };
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
        breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
        storeImages: Array<{
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
        }>;
    } | null;
};

export const StoreAvailabilityFragmentApi = gql`
    fragment StoreAvailabilityFragment on StoreAvailability {
        __typename
        exposed
        availabilityInformation
        availabilityStatus
        store {
            ...StoreDetailFragment
        }
    }
    ${StoreDetailFragmentApi}
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
