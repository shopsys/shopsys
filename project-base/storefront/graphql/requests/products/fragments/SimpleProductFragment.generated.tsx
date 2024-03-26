import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductPriceFragment } from './ProductPriceFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
export type SimpleProductFragment_MainVariant_ = { __typename: 'MainVariant', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: Types.AvailabilityStatusEnum } };

export type SimpleProductFragment_RegularProduct_ = { __typename: 'RegularProduct', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: Types.AvailabilityStatusEnum } };

export type SimpleProductFragment_Variant_ = { __typename: 'Variant', id: number, uuid: string, catalogNumber: string, fullName: string, slug: string, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }>, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, availability: { __typename: 'Availability', name: string, status: Types.AvailabilityStatusEnum } };

export type SimpleProductFragment = SimpleProductFragment_MainVariant_ | SimpleProductFragment_RegularProduct_ | SimpleProductFragment_Variant_;


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
    
export const SimpleProductFragment = gql`
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
    ...ImageFragment
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
    ${ProductPriceFragment}
${ImageFragment}
${SimpleBrandFragment}
${SimpleFlagFragment}
${AvailabilityFragment}`;