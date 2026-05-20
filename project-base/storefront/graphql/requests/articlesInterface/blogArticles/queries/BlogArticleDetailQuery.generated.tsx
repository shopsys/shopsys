// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { BlogArticleDetailFragment } from '../fragments/BlogArticleDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogArticleDetailQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeBlogArticleDetailQuery = { __typename?: 'Query', blogArticle: { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: any | null, status: string, seoTitle: string | null, seoMetaDescription: string | null, seoH1: string | null, mainBlogCategoryUuid: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> } | null };


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