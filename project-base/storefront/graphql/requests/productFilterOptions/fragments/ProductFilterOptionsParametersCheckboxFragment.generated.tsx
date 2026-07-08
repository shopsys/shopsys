// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductFilterOptionsParametersCheckboxFragment = (
  { __typename: 'ParameterCheckboxFilterOption' }
  & Pick<Types.TypeParameterCheckboxFilterOption, 'name' | 'uuid' | 'isCollapsed'>
  & { values: Array<(
    { __typename: 'ParameterValueFilterOption' }
    & Pick<Types.TypeParameterValueFilterOption, 'uuid' | 'text' | 'count' | 'isSelected'>
  )> }
);

export const ProductFilterOptionsParametersCheckboxFragment = gql`
    fragment ProductFilterOptionsParametersCheckboxFragment on ParameterCheckboxFilterOption {
  name
  uuid
  __typename
  values {
    __typename
    uuid
    text
    count
    isSelected
  }
  isCollapsed
}
    `;