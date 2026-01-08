// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductFilterOptionsParametersSliderFragment = { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null };

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