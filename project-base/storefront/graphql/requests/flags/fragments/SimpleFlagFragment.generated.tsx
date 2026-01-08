// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleFlagFragment = { __typename: 'Flag', uuid: string, name: string, rgbColor: string };

export const SimpleFlagFragment = gql`
    fragment SimpleFlagFragment on Flag {
  __typename
  uuid
  name
  rgbColor
}
    `;