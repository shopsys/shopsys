// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedBrandFragment } from '../fragments/ListedBrandFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBrandsQueryVariables = Exact<{ [key: string]: never; }>;


export type TypeBrandsQuery = { brands: Array<{ __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }> };


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