// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
export type TypeProductFilterOptionsBrandsFragment = (
  { __typename: 'BrandFilterOption' }
  & Pick<Types.TypeBrandFilterOption, 'count'>
  & { brand: (
    { __typename: 'Brand' }
    & Pick<Types.TypeBrand, 'uuid' | 'name'>
  ) }
);

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