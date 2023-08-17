import * as Types from '../../types';

import gql from 'graphql-tag';
import { CountryFragmentApi } from '../../countries/fragments/CountryFragment.generated';
import { OpeningHoursFragmentApi } from './OpeningHoursFragment.generated';
import { BreadcrumbFragmentApi } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { ImageSizesFragmentApi } from '../../images/fragments/ImageSizesFragment.generated';
export type StoreDetailFragmentApi = {
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
};

export const StoreDetailFragmentApi = gql`
    fragment StoreDetailFragment on Store {
        __typename
        uuid
        slug
        storeName: name
        description
        street
        city
        postcode
        country {
            ...CountryFragment
        }
        openingHours {
            ...OpeningHoursFragment
        }
        contactInfo
        specialMessage
        locationLatitude
        locationLongitude
        breadcrumb {
            ...BreadcrumbFragment
        }
        storeImages: images(sizes: ["default", "thumbnail"]) {
            ...ImageSizesFragment
        }
    }
    ${CountryFragmentApi}
    ${OpeningHoursFragmentApi}
    ${BreadcrumbFragmentApi}
    ${ImageSizesFragmentApi}
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
