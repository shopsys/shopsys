// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductFilterOptionsParametersSliderFragment = (
  { __typename: 'ParameterSliderFilterOption' }
  & Pick<Types.TypeParameterSliderFilterOption, 'name' | 'uuid' | 'minimalValue' | 'maximalValue' | 'isCollapsed' | 'selectedValue' | 'isSelectable'>
  & { unit: Types.Maybe<(
    { __typename: 'Unit' }
    & Pick<Types.TypeUnit, 'name'>
  )> }
);

export const ProductFilterOptionsParametersSliderFragment = gql`
    fragment ProductFilterOptionsParametersSliderFragment on ParameterSliderFilterOption {
  name
  uuid
  __typename
  minimalValue
  maximalValue
  unit {
    __typename
    name
  }
  isCollapsed
  selectedValue
  isSelectable
}
    `;