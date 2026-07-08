// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { BlogArticleDetailFragment } from '../fragments/BlogArticleDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogArticleDetailQueryVariables = Types.Exact<{
  urlSlug?: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeBlogArticleDetailQuery = (
  { __typename?: 'Query' }
  & { blogArticle: Types.Maybe<(
    { __typename: 'BlogArticle' }
    & Pick<Types.TypeBlogArticle, 'id' | 'uuid' | 'name' | 'slug' | 'link' | 'text' | 'publishDate' | 'status' | 'seoTitle' | 'seoMetaDescription' | 'seoH1' | 'mainBlogCategoryUuid'>
    & { mainImage: Types.Maybe<(
      { __typename: 'Image' }
      & Pick<Types.TypeImage, 'name' | 'url'>
    )>, breadcrumb: Array<(
      { __typename: 'Link' }
      & Pick<Types.TypeLink, 'name' | 'slug'>
    )>, hreflangLinks: Array<(
      { __typename?: 'HreflangLink' }
      & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
    )>, blogCategories: Array<(
      { __typename: 'BlogCategory' }
      & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
      & { parent: Types.Maybe<(
        { __typename?: 'BlogCategory' }
        & Pick<Types.TypeBlogCategory, 'name'>
      )> }
    )> }
  )> }
);


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