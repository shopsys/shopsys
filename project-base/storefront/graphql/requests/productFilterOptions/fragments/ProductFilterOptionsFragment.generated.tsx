// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductFilterOptionsBrandsFragment } from './ProductFilterOptionsBrandsFragment.generated';
import { ProductFilterOptionsFlagsFragment } from './ProductFilterOptionsFlagsFragment.generated';
import { ProductFilterOptionsParametersCheckboxFragment } from './ProductFilterOptionsParametersCheckboxFragment.generated';
import { ProductFilterOptionsParametersColorFragment } from './ProductFilterOptionsParametersColorFragment.generated';
import { ProductFilterOptionsParametersSliderFragment } from './ProductFilterOptionsParametersSliderFragment.generated';
export type TypeProductFilterOptionsFragment = { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<
    | { __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> }
    | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean, colorIcon: { url: string, anchorText: string } | null }> }
    | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }
  > | null };

export const ProductFilterOptionsFragment = gql`
    fragment ProductFilterOptionsFragment on ProductFilterOptions {
  __typename
  minimalPrice
  maximalPrice
  brands {
    ...ProductFilterOptionsBrandsFragment
  }
  inStock
  flags {
    ...ProductFilterOptionsFlagsFragment
  }
  parameters {
    ...ProductFilterOptionsParametersCheckboxFragment
    ...ProductFilterOptionsParametersColorFragment
    ...ProductFilterOptionsParametersSliderFragment
  }
}
    ${ProductFilterOptionsBrandsFragment}
${ProductFilterOptionsFlagsFragment}
${ProductFilterOptionsParametersCheckboxFragment}
${ProductFilterOptionsParametersColorFragment}
${ProductFilterOptionsParametersSliderFragment}`;