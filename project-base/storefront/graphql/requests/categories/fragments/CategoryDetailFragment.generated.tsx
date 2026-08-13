// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { HreflangLinksFragment } from '../../hreflangLinks/fragments/HreflangLinksFragment.generated';
import { BreadcrumbFragment } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { CategoryPreviewFragment } from './CategoryPreviewFragment.generated';
import { ListedProductConnectionPreviewFragment } from '../../products/fragments/ListedProductConnectionPreviewFragment.generated';
import { CategoryBestsellerFragment } from './CategoryBestsellerFragment.generated';
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product is out of stock with a known expected restocking date */
  | 'ExpectedRestock'
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

/** Category automated filter types */
export type TypeCategoryAutomatedFilterEnum =
  | 'newProducts'
  | 'onStock'
  | 'specialPrices';

/** One of possible ordering modes for product */
export type TypeProductOrderingModeEnum =
  /** Order by name ascending */
  | 'NAME_ASC'
  /** Order by name descending */
  | 'NAME_DESC'
  /** Order by price ascending */
  | 'PRICE_ASC'
  /** Order by price descending */
  | 'PRICE_DESC'
  /** Order by priority */
  | 'PRIORITY'
  /** Order by relevance */
  | 'RELEVANCE';

export type TypeCategoryDetailFragment = { __typename: 'Category', id: number, uuid: string, slug: string, originalCategorySlug: string | null, zboziCategory: string | null, name: string, description: string | null, seoH1: string | null, seoTitle: string | null, seoMetaDescription: string | null, automatedFilters: Array<Types.TypeCategoryAutomatedFilterEnum>, images: Array<{ __typename: 'Image', name: string | null, url: string }>, readyCategorySeoMixLinks: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ hreflang: string, href: string }>, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, categoryHierarchy: Array<{ id: number, name: string }>, children: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } }>, products: { __typename: 'ProductConnection', orderingMode: Types.TypeProductOrderingModeEnum, defaultOrderingMode: Types.TypeProductOrderingModeEnum | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<
        | { __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> }
        | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean, colorIcon: { url: string, anchorText: string } | null }> }
        | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }
      > | null } }, bestsellers: Array<
    | { __typename: 'MainVariant', variantsCount: number, id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
    | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
    | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, stockQuantity: number | null, isAllowedNegativeStock: boolean, isSellingDenied: boolean, isCurrentlyOutOfStock: boolean, expectedRestockingDate: string | null, availableStoresCount: number | null, catalogNumber: string, isMainVariant: boolean, isInquiryType: boolean, mainVariant: { slug: string } | null, unit: { __typename: 'Unit', name: string }, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, percentageDiscount: number | null, basicPrice: { __typename: 'Price', priceWithVat: string } }, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, brand: { __typename: 'Brand', name: string } | null, categories: Array<{ __typename: 'Category', name: string }> }
  > };

export const CategoryDetailFragment = gql`
    fragment CategoryDetailFragment on Category {
  __typename
  id
  uuid
  slug
  originalCategorySlug
  zboziCategory
  name
  description
  images {
    ...ImageFragment
  }
  seoH1
  seoTitle
  seoMetaDescription
  readyCategorySeoMixLinks {
    __typename
    name
    slug
  }
  hreflangLinks {
    ...HreflangLinksFragment
  }
  breadcrumb {
    ...BreadcrumbFragment
  }
  categoryHierarchy {
    id
    name
  }
  children {
    ...CategoryPreviewFragment
  }
  products(orderingMode: $orderingMode, filter: $filter) {
    ...ListedProductConnectionPreviewFragment
  }
  bestsellers {
    ...CategoryBestsellerFragment
  }
  automatedFilters
}
    ${ImageFragment}
${HreflangLinksFragment}
${BreadcrumbFragment}
${CategoryPreviewFragment}
${ListedProductConnectionPreviewFragment}
${CategoryBestsellerFragment}`;