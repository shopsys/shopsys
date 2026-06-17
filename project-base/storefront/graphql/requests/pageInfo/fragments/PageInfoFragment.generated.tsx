// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypePageInfoFragment = { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null };

export const PageInfoFragment = gql`
    fragment PageInfoFragment on PageInfo {
  __typename
  hasNextPage
  hasPreviousPage
  endCursor
}
    `;