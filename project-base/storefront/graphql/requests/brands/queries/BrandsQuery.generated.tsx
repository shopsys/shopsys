// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedBrandFragment } from '../fragments/ListedBrandFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBrandsQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeBrandsQuery = (
  { __typename?: 'Query' }
  & { brands: Array<(
    { __typename: 'Brand' }
    & Pick<Types.TypeBrand, 'uuid' | 'name' | 'slug'>
    & { mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )> }
  )> }
);


export const BrandsQueryDocument = gql`
    query BrandsQuery @redisCache(ttl: 3600) {
  brands {
    ...ListedBrandFragment
  }
}
    ${ListedBrandFragment}`;

export function useBrandsQuery(options?: Omit<Urql.UseQueryArgs<TypeBrandsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBrandsQuery, TypeBrandsQueryVariables>({ query: BrandsQueryDocument, ...options });
};