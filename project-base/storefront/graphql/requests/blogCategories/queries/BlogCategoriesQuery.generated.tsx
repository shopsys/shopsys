// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogCategoriesFragment } from '../fragments/BlogCategoriesFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogCategoriesVariables = Types.Exact<{ [key: string]: never; }>;


export type TypeBlogCategories = (
  { __typename?: 'Query' }
  & { blogCategories: Array<(
    { __typename: 'BlogCategory' }
    & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
    & { children: Array<(
      { __typename: 'BlogCategory' }
      & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
      & { children: Array<(
        { __typename: 'BlogCategory' }
        & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
        & { children: Array<(
          { __typename: 'BlogCategory' }
          & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
          & { children: Array<(
            { __typename: 'BlogCategory' }
            & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
            & { parent: Types.Maybe<(
              { __typename?: 'BlogCategory' }
              & Pick<Types.TypeBlogCategory, 'name'>
            )> }
          )>, parent: Types.Maybe<(
            { __typename?: 'BlogCategory' }
            & Pick<Types.TypeBlogCategory, 'name'>
          )> }
        )>, parent: Types.Maybe<(
          { __typename?: 'BlogCategory' }
          & Pick<Types.TypeBlogCategory, 'name'>
        )> }
      )>, parent: Types.Maybe<(
        { __typename?: 'BlogCategory' }
        & Pick<Types.TypeBlogCategory, 'name'>
      )> }
    )>, parent: Types.Maybe<(
      { __typename?: 'BlogCategory' }
      & Pick<Types.TypeBlogCategory, 'name'>
    )> }
  )> }
);


export const BlogCategoriesDocument = gql`
    query BlogCategories @redisCache(ttl: 3600) {
  blogCategories {
    ...BlogCategoriesFragment
  }
}
    ${BlogCategoriesFragment}`;

export function useBlogCategories(options?: Omit<Urql.UseQueryArgs<TypeBlogCategoriesVariables>, 'query'>) {
  return Urql.useQuery<TypeBlogCategories, TypeBlogCategoriesVariables>({ query: BlogCategoriesDocument, ...options });
};