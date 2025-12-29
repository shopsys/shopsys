// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedBrandFragment } from '../fragments/ListedBrandFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBrandsQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeBrandsQuery = { __typename?: 'Query', brands: Array<{ __typename: 'Brand', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }> };


export const BrandsQueryDocument = gql`
    query BrandsQuery {
  brands {
    ...ListedBrandFragment
  }
}
    ${ListedBrandFragment}`;

export function useBrandsQuery(options?: Omit<Urql.UseQueryArgs<TypeBrandsQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBrandsQuery, TypeBrandsQueryVariables>({ query: BrandsQueryDocument, ...options });
};