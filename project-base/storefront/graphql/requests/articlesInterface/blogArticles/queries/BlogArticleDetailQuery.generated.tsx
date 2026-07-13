// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { BlogArticleDetailFragment } from '../fragments/BlogArticleDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogArticleDetailQueryVariables = Exact<{
  urlSlug?: string | null | undefined;
}>;


export type TypeBlogArticleDetailQuery = { blogArticle: { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: string | null, status: string, seoTitle: string | null, seoMetaDescription: string | null, seoH1: string | null, mainBlogCategoryUuid: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ hreflang: string, href: string }>, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { name: string } | null }>, author: { __typename: 'BlogArticleAuthor', uuid: string, name: string, jobTitle: string | null, description: string | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null };


export const BlogArticleDetailQueryDocument = gql`
    query BlogArticleDetailQuery($urlSlug: String) @friendlyUrl {
  blogArticle(urlSlug: $urlSlug) {
    ...BlogArticleDetailFragment
  }
}
    ${BlogArticleDetailFragment}`;

export function useBlogArticleDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeBlogArticleDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeBlogArticleDetailQuery, TypeBlogArticleDetailQueryVariables>({ query: BlogArticleDetailQueryDocument, ...options });
};