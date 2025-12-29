// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductFilterOptionsBrandsFragment = { __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } };

export const ProductFilterOptionsBrandsFragment = gql`
    fragment ProductFilterOptionsBrandsFragment on BrandFilterOption {
  __typename
  count
  brand {
    __typename
    uuid
    name
  }
}
    `;