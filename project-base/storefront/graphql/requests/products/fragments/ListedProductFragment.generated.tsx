import * as Types from '../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragmentApi } from '../../flags/fragments/SimpleFlagFragment.generated';
import { ImageSizesFragmentApi } from '../../images/fragments/ImageSizesFragment.generated';
import { ProductPriceFragmentApi } from './ProductPriceFragment.generated';
import { AvailabilityFragmentApi } from '../../availabilities/fragments/AvailabilityFragment.generated';
import { SimpleBrandFragmentApi } from '../../brands/fragments/SimpleBrandFragment.generated';
export type ListedProductFragment_MainVariant_Api = {
    __typename: 'MainVariant';
    id: number;
    uuid: string;
    slug: string;
    fullName: string;
    name: string;
    stockQuantity: number;
    isSellingDenied: boolean;
    availableStoresCount: number;
    exposedStoresCount: number;
    catalogNumber: string;
    isMainVariant: boolean;
    flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
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
    price: {
        __typename: 'ProductPrice';
        priceWithVat: string;
        priceWithoutVat: string;
        vatAmount: string;
        isPriceFrom: boolean;
    };
    availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
    brand: { __typename: 'Brand'; name: string; slug: string } | null;
    categories: Array<{ __typename: 'Category'; name: string }>;
};

export type ListedProductFragment_RegularProduct_Api = {
    __typename: 'RegularProduct';
    id: number;
    uuid: string;
    slug: string;
    fullName: string;
    name: string;
    stockQuantity: number;
    isSellingDenied: boolean;
    availableStoresCount: number;
    exposedStoresCount: number;
    catalogNumber: string;
    isMainVariant: boolean;
    flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
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
    price: {
        __typename: 'ProductPrice';
        priceWithVat: string;
        priceWithoutVat: string;
        vatAmount: string;
        isPriceFrom: boolean;
    };
    availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
    brand: { __typename: 'Brand'; name: string; slug: string } | null;
    categories: Array<{ __typename: 'Category'; name: string }>;
};

export type ListedProductFragment_Variant_Api = {
    __typename: 'Variant';
    id: number;
    uuid: string;
    slug: string;
    fullName: string;
    name: string;
    stockQuantity: number;
    isSellingDenied: boolean;
    availableStoresCount: number;
    exposedStoresCount: number;
    catalogNumber: string;
    isMainVariant: boolean;
    flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
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
    price: {
        __typename: 'ProductPrice';
        priceWithVat: string;
        priceWithoutVat: string;
        vatAmount: string;
        isPriceFrom: boolean;
    };
    availability: { __typename: 'Availability'; name: string; status: Types.AvailabilityStatusEnumApi };
    brand: { __typename: 'Brand'; name: string; slug: string } | null;
    categories: Array<{ __typename: 'Category'; name: string }>;
};

export type ListedProductFragmentApi =
    | ListedProductFragment_MainVariant_Api
    | ListedProductFragment_RegularProduct_Api
    | ListedProductFragment_Variant_Api;

export const ListedProductFragmentApi = gql`
    fragment ListedProductFragment on Product {
        __typename
        id
        uuid
        slug
        fullName
        name
        stockQuantity
        isSellingDenied
        flags {
            ...SimpleFlagFragment
        }
        mainImage {
            ...ImageSizesFragment
        }
        price {
            ...ProductPriceFragment
        }
        availability {
            ...AvailabilityFragment
        }
        availableStoresCount
        exposedStoresCount
        catalogNumber
        brand {
            ...SimpleBrandFragment
        }
        categories {
            __typename
            name
        }
        isMainVariant
    }
    ${SimpleFlagFragmentApi}
    ${ImageSizesFragmentApi}
    ${ProductPriceFragmentApi}
    ${AvailabilityFragmentApi}
    ${SimpleBrandFragmentApi}
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
