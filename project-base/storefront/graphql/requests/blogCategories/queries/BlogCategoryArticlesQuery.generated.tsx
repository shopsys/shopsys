// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BlogArticleConnectionFragment } from '../../articlesInterface/blogArticles/fragments/BlogArticleConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeBlogCategoryArticlesVariables = Types.Exact<{
  uuid: Types.Scalars['Uuid']['input'];
  endCursor: Types.Scalars['String']['input'];
  pageSize?: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;


export type TypeBlogCategoryArticles = (
  { __typename?: 'Query' }
  & { blogCategory: Types.Maybe<(
    { __typename?: 'BlogCategory' }
    & { blogArticles: (
      { __typename: 'BlogArticleConnection' }
      & Pick<Types.TypeBlogArticleConnection, 'totalCount'>
      & { pageInfo: (
        { __typename: 'PageInfo' }
        & Pick<Types.TypePageInfo, 'hasNextPage' | 'hasPreviousPage' | 'endCursor'>
      ), edges: Types.Maybe<Array<Types.Maybe<(
        { __typename: 'BlogArticleEdge' }
        & { node: Types.Maybe<(
          { __typename: 'BlogArticle' }
          & Pick<Types.TypeBlogArticle, 'uuid' | 'name' | 'link' | 'publishDate' | 'perex' | 'slug'>
          & { mainImage: Types.Maybe<(
            { __typename: 'Image' }
            & Pick<Types.TypeImage, 'name' | 'url'>
          )>, blogCategories: Array<(
            { __typename: 'BlogCategory' }
            & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'link'>
            & { parent: Types.Maybe<(
              { __typename?: 'BlogCategory' }
              & Pick<Types.TypeBlogCategory, 'name'>
            )> }
          )> }
        )> }
      )>>> }
    ) }
  )> }
);


export const BlogCategoryArticlesDocument = gql`
    query BlogCategoryArticles($uuid: Uuid!, $endCursor: String!, $pageSize: Int) {
  blogCategory(uuid: $uuid) {
    blogArticles(after: $endCursor, first: $pageSize) {
      ...BlogArticleConnectionFragment
    }
  }
}
    ${BlogArticleConnectionFragment}`;

export function useBlogCategoryArticles(options: Omit<Urql.UseQueryArgs<TypeBlogCategoryArticlesVariables>, 'query'>) {
  return Urql.useQuery<TypeBlogCategoryArticles, TypeBlogCategoryArticlesVariables>({ query: BlogCategoryArticlesDocument, ...options });
};