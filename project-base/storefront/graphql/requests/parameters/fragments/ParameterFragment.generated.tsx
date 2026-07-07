// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
/** Represents the type of the parameter */
export type TypeParameterTypeEnum =
  | 'CHECKBOX'
  | 'COLOR'
  | 'SLIDER';

export type TypeParameterFragment = { __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> };

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