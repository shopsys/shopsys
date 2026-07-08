// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CategoriesByColumnFragment } from '../fragments/CategoriesByColumnsFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeNavigationQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeNavigationQuery = (
  { __typename?: 'Query' }
  & { navigation: Array<(
    { __typename: 'NavigationItem' }
    & Pick<Types.TypeNavigationItem, 'name' | 'link' | 'routeName'>
    & { categoriesByColumns: Array<(
      { __typename: 'NavigationItemCategoriesByColumns' }
      & Pick<Types.TypeNavigationItemCategoriesByColumns, 'columnNumber'>
      & { categories: Array<(
        { __typename: 'Category' }
        & Pick<Types.TypeCategory, 'uuid' | 'name' | 'slug'>
        & { mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, children: Array<(
          { __typename: 'Category' }
          & Pick<Types.TypeCategory, 'name' | 'slug'>
        )> }
      )> }
    )> }
  )> }
);


export const NavigationQueryDocument = gql`
    query NavigationQuery @redisCache(ttl: 3600) {
  navigation {
    ...CategoriesByColumnFragment
  }
}
    ${CategoriesByColumnFragment}`;

export function useNavigationQuery(options?: Omit<Urql.UseQueryArgs<TypeNavigationQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeNavigationQuery, TypeNavigationQueryVariables>({ query: NavigationQueryDocument, ...options });
};