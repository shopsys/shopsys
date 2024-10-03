import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CartItemFragment } from './CartItemFragment.generated';
export type TypeCartItemModificationsFragment = { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> }, discount: Array<{ __typename?: 'CartItemDiscount', promoCode: string, totalDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, unitDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> | null }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> }, discount: Array<{ __typename?: 'CartItemDiscount', promoCode: string, totalDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, unitDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> | null }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> }, discount: Array<{ __typename?: 'CartItemDiscount', promoCode: string, totalDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, unitDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> | null }>, noLongerAvailableCartItemsDueToQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, product: { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> } | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, stockQuantity: number, availableStoresCount: number, mainVariant: { __typename?: 'MainVariant', slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean }, unit: { __typename?: 'Unit', name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ __typename?: 'Category', name: string }> }, discount: Array<{ __typename?: 'CartItemDiscount', promoCode: string, totalDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, unitDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }> | null }> };


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
    
export const CartItemModificationsFragment = gql`
    fragment CartItemModificationsFragment on CartItemModificationsResult {
  __typename
  noLongerListableCartItems {
    ...CartItemFragment
  }
  cartItemsWithModifiedPrice {
    ...CartItemFragment
  }
  cartItemsWithChangedQuantity {
    ...CartItemFragment
  }
  noLongerAvailableCartItemsDueToQuantity {
    ...CartItemFragment
  }
}
    ${CartItemFragment}`;