// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedCategoryFragment } from './ListedCategoryFragment.generated';
export type TypeListedCategoryConnectionFragment = { __typename: 'CategoryConnection', totalCount: number, edges: Array<{ __typename: 'CategoryEdge', node: { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } } | null } | null> | null };

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