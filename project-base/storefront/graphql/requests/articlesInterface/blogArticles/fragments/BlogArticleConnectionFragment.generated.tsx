// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { PageInfoFragment } from '../../../pageInfo/fragments/PageInfoFragment.generated';
import { ListedBlogArticleFragment } from './ListedBlogArticleFragment.generated';
export type TypeBlogArticleConnectionFragment = (
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
);

export const BlogArticleConnectionFragment = gql`
    fragment BlogArticleConnectionFragment on BlogArticleConnection {
  __typename
  totalCount
  pageInfo {
    ...PageInfoFragment
  }
  edges {
    __typename
    node {
      ...ListedBlogArticleFragment
    }
  }
}
    ${PageInfoFragment}
${ListedBlogArticleFragment}`;