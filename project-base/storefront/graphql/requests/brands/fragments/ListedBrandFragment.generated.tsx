// @ts-nocheck
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