// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleCategoryFragment = { __typename: 'Category', uuid: string, name: string, slug: string };

export const SimpleCategoryFragment = gql`
    fragment SimpleCategoryFragment on Category {
  __typename
  uuid
  name
  slug
}
    `;