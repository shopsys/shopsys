import * as Types from '../../types';

import gql from 'graphql-tag';
import { ListedProductFragmentApi } from './ListedProductFragment.generated';
import { ParameterFragmentApi } from '../../parameters/fragments/ParameterFragment.generated';
export type ComparedProductFragment_MainVariant_Api = {
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
    parameters: Array<{
        __typename: 'Parameter';
        uuid: string;
        name: string;
        visible: boolean;
        values: Array<{ __typename: 'ParameterValue'; uuid: string; text: string }>;
    }>;
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

export type ComparedProductFragment_RegularProduct_Api = {
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
    parameters: Array<{
        __typename: 'Parameter';
        uuid: string;
        name: string;
        visible: boolean;
        values: Array<{ __typename: 'ParameterValue'; uuid: string; text: string }>;
    }>;
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

export type ComparedProductFragment_Variant_Api = {
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
    parameters: Array<{
        __typename: 'Parameter';
        uuid: string;
        name: string;
        visible: boolean;
        values: Array<{ __typename: 'ParameterValue'; uuid: string; text: string }>;
    }>;
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

export type ComparedProductFragmentApi =
    | ComparedProductFragment_MainVariant_Api
    | ComparedProductFragment_RegularProduct_Api
    | ComparedProductFragment_Variant_Api;

export const ComparedProductFragmentApi = gql`
    fragment ComparedProductFragment on Product {
        ...ListedProductFragment
        parameters {
            ...ParameterFragment
        }
    }
    ${ListedProductFragmentApi}
    ${ParameterFragmentApi}
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
