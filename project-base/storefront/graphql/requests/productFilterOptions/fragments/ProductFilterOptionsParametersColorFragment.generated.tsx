// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductFilterOptionsParametersColorFragment = (
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
);

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