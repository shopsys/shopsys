import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { ProductPriceFragment } from './ProductPriceFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
export type ListedProductFragment_MainVariant_ = { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: Types.AvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> };

export type ListedProductFragment_RegularProduct_ = { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: Types.AvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> };

export type ListedProductFragment_Variant_ = { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, name: string, stockQuantity: number, isSellingDenied: boolean, availableStoresCount: number, catalogNumber: string, isMainVariant: boolean, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, availability: { __typename: 'Availability', name: string, status: Types.AvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename: 'Category', name: string }> };

export type ListedProductFragment = ListedProductFragment_MainVariant_ | ListedProductFragment_RegularProduct_ | ListedProductFragment_Variant_;


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CartInterface": [
      "Cart"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "PriceInterface": [
      "Price",
      "ProductPrice"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    
export const ListedProductFragment = gql`
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
    ...ImageFragment
  }
  price {
    ...ProductPriceFragment
  }
  availability {
    ...AvailabilityFragment
  }
  availableStoresCount
  catalogNumber
  brand {
    ...SimpleBrandFragment
  }
  categories {
    __typename
    name
  }
  isMainVariant
  ... on Variant {
    mainVariant {
      slug
    }
  }
}
    ${SimpleFlagFragment}
${ImageFragment}
${ProductPriceFragment}
${AvailabilityFragment}
${SimpleBrandFragment}`;