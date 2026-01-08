// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedCategoryFragment } from '../fragments/ListedCategoryFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePromotedCategoriesQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypePromotedCategoriesQuery = { __typename?: 'Query', promotedCategories: Array<{ __typename: 'Category', uuid: string, name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, products: { __typename: 'ProductConnection', totalCount: number } }> };


export const PromotedCategoriesQueryDocument = gql`
    query PromotedCategoriesQuery @redisCache(ttl: 3600) {
  promotedCategories {
    ...ListedCategoryFragment
  }
}
    ${ListedCategoryFragment}`;

export function usePromotedCategoriesQuery(options?: Omit<Urql.UseQueryArgs<TypePromotedCategoriesQueryVariables>, 'query'>) {
  return Urql.useQuery<TypePromotedCategoriesQuery, TypePromotedCategoriesQueryVariables>({ query: PromotedCategoriesQueryDocument, ...options });
};