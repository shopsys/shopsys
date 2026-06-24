// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
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