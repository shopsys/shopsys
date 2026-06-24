// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
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