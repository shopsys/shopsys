import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragment } from '../../flags/fragments/SimpleFlagFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { AvailabilityFragment } from '../../availabilities/fragments/AvailabilityFragment.generated';
import { ProductPriceFragment } from '../../products/fragments/ProductPriceFragment.generated';
import { StoreAvailabilityFragment } from '../../storeAvailabilities/fragments/StoreAvailabilityFragment.generated';
import { SimpleBrandFragment } from '../../brands/fragments/SimpleBrandFragment.generated';
import { CartItemDiscountFragment } from './CartItemDiscountFragment.generated';
export type TypeCartItemFragment = { __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, storeAvailabilities: Array<{ __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: Types.TypeAvailabilityStatusEnum, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, latitude: string | null, longitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null }>, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, storeAvailabilities: Array<{ __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: Types.TypeAvailabilityStatusEnum, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, latitude: string | null, longitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null }>, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, storeAvailabilities: Array<{ __typename: 'StoreAvailability', availabilityInformation: string, availabilityStatus: Types.TypeAvailabilityStatusEnum, store: { __typename: 'Store', uuid: string, slug: string, description: string | null, street: string, city: string, postcode: string, contactInfo: string | null, specialMessage: string | null, latitude: string | null, longitude: string | null, storeName: string, country: { __typename: 'Country', name: string, code: string }, openingHours: { __typename?: 'OpeningHours', status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ __typename?: 'OpeningHoursOfDay', date: any, dayOfWeek: number, openingHoursRanges: Array<{ __typename?: 'OpeningHoursRange', openingTime: string, closingTime: string }> }> }, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, storeImages: Array<{ __typename: 'Image', name: string | null, url: string }> } | null }>, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> }, discount: Array<{ __typename: 'CartItemDiscount', promoCode: string, totalDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, unitDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> | null };


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
    
export const CartItemFragment = gql`
    fragment CartItemFragment on CartItem {
  __typename
  uuid
  quantity
  product {
    __typename
    id
    uuid
    slug
    ... on Variant {
      mainVariant {
        slug
      }
    }
    fullName
    catalogNumber
    stockQuantity
    flags {
      ...SimpleFlagFragment
    }
    mainImage {
      ...ImageFragment
    }
    stockQuantity
    availability {
      ...AvailabilityFragment
    }
    price {
      ...ProductPriceFragment
    }
    availableStoresCount
    storeAvailabilities {
      ...StoreAvailabilityFragment
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
  }
  discount {
    ...CartItemDiscountFragment
  }
}
    ${SimpleFlagFragment}
${ImageFragment}
${AvailabilityFragment}
${ProductPriceFragment}
${StoreAvailabilityFragment}
${SimpleBrandFragment}
${CartItemDiscountFragment}`;