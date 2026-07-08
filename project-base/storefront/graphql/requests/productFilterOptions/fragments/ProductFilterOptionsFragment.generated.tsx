// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductFilterOptionsBrandsFragment } from './ProductFilterOptionsBrandsFragment.generated';
import { ProductFilterOptionsFlagsFragment } from './ProductFilterOptionsFlagsFragment.generated';
import { ProductFilterOptionsParametersCheckboxFragment } from './ProductFilterOptionsParametersCheckboxFragment.generated';
import { ProductFilterOptionsParametersColorFragment } from './ProductFilterOptionsParametersColorFragment.generated';
import { ProductFilterOptionsParametersSliderFragment } from './ProductFilterOptionsParametersSliderFragment.generated';
export type TypeProductFilterOptionsFragment = (
  { __typename: 'ProductFilterOptions' }
  & Pick<Types.TypeProductFilterOptions, 'minimalPrice' | 'maximalPrice' | 'inStock'>
  & { brands: Types.Maybe<Array<(
    { __typename: 'BrandFilterOption' }
    & Pick<Types.TypeBrandFilterOption, 'count'>
    & { brand: (
      { __typename: 'Brand' }
      & Pick<Types.TypeBrand, 'uuid' | 'name'>
    ) }
  )>>, flags: Types.Maybe<Array<(
    { __typename: 'FlagFilterOption' }
    & Pick<Types.TypeFlagFilterOption, 'count' | 'isSelected'>
    & { flag: (
      { __typename: 'Flag' }
      & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
    ) }
  )>>, parameters: Types.Maybe<Array<(
    { __typename: 'ParameterCheckboxFilterOption' }
    & Pick<Types.TypeParameterCheckboxFilterOption, 'name' | 'uuid' | 'isCollapsed'>
    & { values: Array<(
      { __typename: 'ParameterValueFilterOption' }
      & Pick<Types.TypeParameterValueFilterOption, 'uuid' | 'text' | 'count' | 'isSelected'>
    )> }
  ) | (
    { __typename: 'ParameterColorFilterOption' }
    & Pick<Types.TypeParameterColorFilterOption, 'name' | 'uuid' | 'isCollapsed'>
    & { values: Array<(
      { __typename: 'ParameterValueColorFilterOption' }
      & Pick<Types.TypeParameterValueColorFilterOption, 'uuid' | 'text' | 'count' | 'rgbHex' | 'isSelected'>
      & { colorIcon: Types.Maybe<(
        { __typename?: 'File' }
        & Pick<Types.TypeFile, 'url' | 'anchorText'>
      )> }
    )> }
  ) | (
    { __typename: 'ParameterSliderFilterOption' }
    & Pick<Types.TypeParameterSliderFilterOption, 'name' | 'uuid' | 'minimalValue' | 'maximalValue' | 'isCollapsed' | 'selectedValue' | 'isSelectable'>
    & { unit: Types.Maybe<(
      { __typename: 'Unit' }
      & Pick<Types.TypeUnit, 'name'>
    )> }
  )>> }
);

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