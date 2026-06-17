// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeListedBrandFragment = { __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

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