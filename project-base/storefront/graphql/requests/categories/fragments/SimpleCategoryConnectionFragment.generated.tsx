// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleCategoryFragment } from './SimpleCategoryFragment.generated';
export type TypeSimpleCategoryConnectionFragment = (
  { __typename: 'CategoryConnection' }
  & Pick<Types.TypeCategoryConnection, 'totalCount'>
  & { edges: Types.Maybe<Array<Types.Maybe<(
    { __typename: 'CategoryEdge' }
    & { node: Types.Maybe<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
    )> }
  )>>> }
);

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