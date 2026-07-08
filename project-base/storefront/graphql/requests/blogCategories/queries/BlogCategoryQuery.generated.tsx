// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogCategoryDetailFragment } from '../fragments/BlogCategoryDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogCategoryQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeBlogCategoryQuery = (
  { __typename?: 'Query' }
  & { blogCategory: Types.Maybe<(
    { __typename: 'BlogCategory' }
    & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'seoTitle' | 'seoMetaDescription' | 'description' | 'articlesTotalCount'>
    & { breadcrumb: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )>, mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, hreflangLinks: Array<(
      { __typename?: 'HreflangLink' }
      & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
    )> }
  )> }
);


export const BlogCategoryQueryDocument = gql`
    query BlogCategoryQuery($urlSlug: String) @friendlyUrl {
  blogCategory(urlSlug: $urlSlug) {
    ...BlogCategoryDetailFragment
  }
}
    ${BlogCategoryDetailFragment}`;

export function useBlogCategoryQuery(options?: Omit<Urql.UseQueryArgs<TypeBlogCategoryQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBlogCategoryQuery, TypeBlogCategoryQueryVariables>({ query: BlogCategoryQueryDocument, ...options });
};