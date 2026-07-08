// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ListedCategoryFragment } from '../fragments/ListedCategoryFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypePromotedCategoriesQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypePromotedCategoriesQuery = (
  { __typename?: 'Query' }
  & { promotedCategories: Array<(
    { __typename: 'Category' }
    & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
    & { mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, products: (
      { __typename: 'ProductConnection' }
      & Pick<Types.TypeProductConnection, 'totalCount'>
    ) }
  )> }
);


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