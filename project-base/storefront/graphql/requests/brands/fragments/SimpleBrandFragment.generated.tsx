// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleBrandFragment = { __typename: 'Brand', name: string, slug: string };

export const SimpleBrandFragment = gql`
    fragment SimpleBrandFragment on Brand {
  __typename
  name
  slug
}
    `;