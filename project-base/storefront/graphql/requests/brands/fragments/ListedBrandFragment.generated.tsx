// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeListedBrandFragment = (
  { __typename: 'Brand' }
  & Pick<Types.TypeBrand, 'uuid' | 'name' | 'slug'>
  & { mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )> }
);

export const ListedBrandFragment = gql`
    fragment ListedBrandFragment on Brand {
  __typename
  uuid
  name
  slug
  mainImage {
    ...ImageFragment
  }
}
    ${ImageFragment}`;