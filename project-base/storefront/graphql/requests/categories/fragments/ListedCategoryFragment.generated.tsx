// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeListedCategoryFragment = { __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } };

export const ListedCategoryFragment = gql`
    fragment ListedCategoryFragment on Category {
  __typename
  uuid
  name
  slug
  mainImage {
    ...ImageFragment
  }
  products {
    __typename
    totalCount
  }
}
    ${ImageFragment}`;