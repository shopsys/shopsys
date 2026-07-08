// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeListedCategoryFragment = (
  { __typename: 'Category' }
  & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
  & { mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, products: (
    { __typename: 'ProductConnection' }
    & Pick<Types.TypeProductConnection, 'totalCount'>
  ) }
);

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