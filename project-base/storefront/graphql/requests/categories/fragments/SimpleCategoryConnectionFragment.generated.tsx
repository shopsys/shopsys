// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCategoryFragment } from './SimpleCategoryFragment.generated';
export type TypeSimpleCategoryConnectionFragment = { __typename: 'CategoryConnection', totalCount: number, edges: Array<{ __typename: 'CategoryEdge', node: { __typename: 'Category', uuid: string, name: string, slug: string } | null } | null> | null };

export const SimpleCategoryConnectionFragment = gql`
    fragment SimpleCategoryConnectionFragment on CategoryConnection {
  __typename
  totalCount
  edges {
    __typename
    node {
      ...SimpleCategoryFragment
    }
  }
}
    ${SimpleCategoryFragment}`;