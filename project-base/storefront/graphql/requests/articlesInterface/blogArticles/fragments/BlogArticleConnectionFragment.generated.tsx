// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { PageInfoFragment } from '../../../pageInfo/fragments/PageInfoFragment.generated';
import { ListedBlogArticleFragment } from './ListedBlogArticleFragment.generated';
export type TypeBlogArticleConnectionFragment = { __typename: 'BlogArticleConnection', totalCount: number, pageInfo: { __typename: 'PageInfo', hasNextPage: boolean, hasPreviousPage: boolean, endCursor: string | null }, edges: Array<{ __typename: 'BlogArticleEdge', node: { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: any, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> } | null } | null> | null };

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