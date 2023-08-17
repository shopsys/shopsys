import * as Types from '../../types';

import gql from 'graphql-tag';
import { ProductPriceFragmentApi } from './ProductPriceFragment.generated';
import { ImageSizesFragmentApi } from '../../images/fragments/ImageSizesFragment.generated';
import { SimpleBrandFragmentApi } from '../../brands/fragments/SimpleBrandFragment.generated';
import { SimpleFlagFragmentApi } from '../../flags/fragments/SimpleFlagFragment.generated';
import { AvailabilityFragmentApi } from '../../availabilities/fragments/AvailabilityFragment.generated';
export type SimpleProductFragment_MainVariant_Api = {
    __typename: 'MainVariant';
    id: number;
    uuid: string;
    catalogNumber: string;
    fullName: string;
    slug: string;
    price: {
        __typename: 'ProductPrice';
        priceWithVat: string;
        priceWithoutVat: string;
        vatAmount: string;
        isPriceFrom: boolean;
    };
    mainImage: {
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
    unit: { __typename?: 'Unit'; name: string };
    brand: { __typename: 'Brand'; name: string; slug: string } | null;
    categories: Array<{ __typename?: 'Category'; name: string }>;
    flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
    availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
};

export type SimpleProductFragment_RegularProduct_Api = {
    __typename: 'RegularProduct';
    id: number;
    uuid: string;
    catalogNumber: string;
    fullName: string;
    slug: string;
    price: {
        __typename: 'ProductPrice';
        priceWithVat: string;
        priceWithoutVat: string;
        vatAmount: string;
        isPriceFrom: boolean;
    };
    mainImage: {
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
    unit: { __typename?: 'Unit'; name: string };
    brand: { __typename: 'Brand'; name: string; slug: string } | null;
    categories: Array<{ __typename?: 'Category'; name: string }>;
    flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
    availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
};

export type SimpleProductFragment_Variant_Api = {
    __typename: 'Variant';
    id: number;
    uuid: string;
    catalogNumber: string;
    fullName: string;
    slug: string;
    price: {
        __typename: 'ProductPrice';
        priceWithVat: string;
        priceWithoutVat: string;
        vatAmount: string;
        isPriceFrom: boolean;
    };
    mainImage: {
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
    unit: { __typename?: 'Unit'; name: string };
    brand: { __typename: 'Brand'; name: string; slug: string } | null;
    categories: Array<{ __typename?: 'Category'; name: string }>;
    flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
    availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
};

export type SimpleProductFragmentApi =
    | SimpleProductFragment_MainVariant_Api
    | SimpleProductFragment_RegularProduct_Api
    | SimpleProductFragment_Variant_Api;

export const SimpleProductFragmentApi = gql`
    fragment SimpleProductFragment on Product {
        __typename
        id
        uuid
        catalogNumber
        fullName
        slug
        price {
            ...ProductPriceFragment
        }
        mainImage {
            ...ImageSizesFragment
        }
        unit {
            name
        }
        brand {
            ...SimpleBrandFragment
        }
        categories {
            name
        }
        flags {
            ...SimpleFlagFragment
        }
        availability {
            ...AvailabilityFragment
        }
    }
    ${ProductPriceFragmentApi}
    ${ImageSizesFragmentApi}
    ${SimpleBrandFragmentApi}
    ${SimpleFlagFragmentApi}
    ${AvailabilityFragmentApi}
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
