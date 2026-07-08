// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedCategoryFragment } from './ListedCategoryFragment.generated';
export type TypeListedCategoryConnectionFragment = (
  { __typename: 'CategoryConnection' }
  & Pick<Types.TypeCategoryConnection, 'totalCount'>
  & { edges: Types.Maybe<Array<Types.Maybe<(
    { __typename: 'CategoryEdge' }
    & { node: Types.Maybe<(
      { __typename: 'Category' }
      & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, products: (
        { __typename: 'ProductConnection' }
        & Pick<Types.TypeProductConnection, 'totalCount'>
      ) }
    )> }
  )>>> }
);

export const ListedCategoryConnectionFragment = gql`
    fragment ListedCategoryConnectionFragment on CategoryConnection {
  __typename
  totalCount
  edges {
    __typename
    node {
      ...ListedCategoryFragment
    }
  }
}
    ${ListedCategoryFragment}`;