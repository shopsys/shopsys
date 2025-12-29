// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeParameterFragment = { __typename: 'Parameter', uuid: string, name: string, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string }> };

export const ParameterFragment = gql`
    fragment ParameterFragment on Parameter {
  __typename
  uuid
  name
  group
  unit {
    __typename
    name
  }
  values {
    __typename
    uuid
    text
  }
}
    `;