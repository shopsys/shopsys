// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragment } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { HreflangLinksFragment } from '../../hreflangLinks/fragments/HreflangLinksFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { ListedProductConnectionPreviewFragment } from '../../products/fragments/ListedProductConnectionPreviewFragment.generated';
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

export type TypeBrandDetailFragment = { __typename: 'Brand', id: number, uuid: string, slug: string, name: string, seoH1: string | null, seoTitle: string | null, seoMetaDescription: string | null, description: string | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ hreflang: string, href: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', orderingMode: Types.TypeProductOrderingModeEnum, defaultOrderingMode: Types.TypeProductOrderingModeEnum | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<
        | { __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> }
        | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean, colorIcon: { url: string, anchorText: string } | null }> }
        | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }
      > | null } } };

export const BrandDetailFragment = gql`
    fragment BrandDetailFragment on Brand {
  __typename
  id
  uuid
  slug
  breadcrumb {
    ...BreadcrumbFragment
  }
  name
  seoH1
  seoTitle
  seoMetaDescription
  hreflangLinks {
    ...HreflangLinksFragment
  }
  description
  mainImage {
    ...ImageFragment
  }
  products(orderingMode: $orderingMode, filter: $filter) {
    ...ListedProductConnectionPreviewFragment
  }
}
    ${BreadcrumbFragment}
${HreflangLinksFragment}
${ImageFragment}
${ListedProductConnectionPreviewFragment}`;