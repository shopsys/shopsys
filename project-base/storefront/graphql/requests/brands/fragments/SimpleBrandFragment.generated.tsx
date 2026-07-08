// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeSimpleBrandFragment = (
  { __typename: 'Brand' }
  & Pick<Types.TypeBrand, 'name' | 'slug'>
);

export const SimpleBrandFragment = gql`
    fragment SimpleBrandFragment on Brand {
  __typename
  name
  slug
}
    `;