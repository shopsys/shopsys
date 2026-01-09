// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductFilterOptionsParametersColorFragment = { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean, colorIcon: { __typename?: 'File', url: string, anchorText: string } | null }> };

export const ProductFilterOptionsParametersColorFragment = gql`
    fragment ProductFilterOptionsParametersColorFragment on ParameterColorFilterOption {
  name
  uuid
  __typename
  values {
    __typename
    uuid
    text
    count
    rgbHex
    isSelected
    colorIcon {
      url
      anchorText
    }
  }
  isCollapsed
}
    `;