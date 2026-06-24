// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleBrandFragment = { __typename: 'Brand', name: string, slug: string };

export const SimpleBrandFragment = gql`
    fragment SimpleBrandFragment on Brand {
  __typename
  name
  slug
}
    `;