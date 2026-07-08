// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeParameterFragment = (
  { __typename: 'Parameter' }
  & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
  & { unit: Types.Maybe<(
    { __typename: 'Unit' }
    & Pick<Types.TypeUnit, 'name'>
  )>, values: Array<(
    { __typename: 'ParameterValue' }
    & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
    & { colorIcon: Types.Maybe<(
      { __typename?: 'File' }
      & Pick<Types.TypeFile, 'url' | 'anchorText'>
    )> }
  )> }
);

export const ParameterFragment = gql`
    fragment ParameterFragment on Parameter {
  __typename
  uuid
  name
  type
  group
  unit {
    __typename
    name
  }
  values {
    __typename
    uuid
    text
    rgbHex
    colorIcon {
      url
      anchorText
    }
  }
}
    `;