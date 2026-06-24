// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BrandDetailFragment } from '../fragments/BrandDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Represents a parameter filter */
export type TypeParameterFilter = {
  /** The parameter maximal value (for parameters with "slider" type) */
  maximalValue?: number | null | undefined;
  /** The parameter minimal value (for parameters with "slider" type) */
  minimalValue?: number | null | undefined;
  /** Uuid of filtered parameter */
  parameter: string;
  /** Array of uuids representing parameter values to be filtered by */
  values: Array<string>;
};

/** Represents a product filter */
export type TypeProductFilter = {
  /** Array of uuids of brands filter */
  brands?: Array<string> | null | undefined;
  /** Array of uuids of flags filter */
  flags?: Array<string> | null | undefined;
  /** Maximal price filter */
  maximalPrice?: string | null | undefined;
  /** Minimal price filter */
  minimalPrice?: string | null | undefined;
  /** Only in stock filter */
  onlyInStock?: boolean | null | undefined;
  /** Parameter filter */
  parameters?: Array<TypeParameterFilter> | null | undefined;
};

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

export type TypeBrandDetailQueryVariables = Exact<{
  urlSlug?: string | null | undefined;
  orderingMode?: Types.TypeProductOrderingModeEnum | null | undefined;
  filter?: Types.TypeProductFilter | null | undefined;
}>;


export type TypeBrandDetailQuery = { brand: { __typename: 'Brand', id: number, uuid: string, slug: string, name: string, seoH1: string | null, seoTitle: string | null, seoMetaDescription: string | null, description: string | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ hreflang: string, href: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', orderingMode: Types.TypeProductOrderingModeEnum, defaultOrderingMode: Types.TypeProductOrderingModeEnum | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<
          | { __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> }
          | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean, colorIcon: { url: string, anchorText: string } | null }> }
          | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }
        > | null } } } | null };


export const BrandDetailQueryDocument = gql`
    query BrandDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) @friendlyUrl {
  brand(urlSlug: $urlSlug) {
    ...BrandDetailFragment
  }
}
    ${BrandDetailFragment}`;

export function useBrandDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeBrandDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBrandDetailQuery, TypeBrandDetailQueryVariables>({ query: BrandDetailQueryDocument, ...options });
};