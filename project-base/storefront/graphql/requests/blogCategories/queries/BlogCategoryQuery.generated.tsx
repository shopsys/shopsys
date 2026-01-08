// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogCategoryDetailFragment } from '../fragments/BlogCategoryDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogCategoryQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeBlogCategoryQuery = { __typename?: 'Query', blogCategory: { __typename: 'BlogCategory', uuid: string, name: string, seoTitle: string | null, seoMetaDescription: string | null, description: string | null, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> } | null };


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