// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductFilterOptionsParametersCheckboxFragment = { __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> };

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