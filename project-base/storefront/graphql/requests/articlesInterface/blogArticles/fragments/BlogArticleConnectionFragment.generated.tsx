// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ListedBlogArticleFragment } from './ListedBlogArticleFragment.generated';
export type TypeBlogArticleConnectionFragment = { __typename: 'BlogArticleConnection', edges: Array<{ __typename: 'BlogArticleEdge', node: { __typename: 'BlogArticle', uuid: string, name: string, link: string, publishDate: string | null, perex: string | null, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { name: string } | null }> } | null } | null> | null };

export const BlogArticleConnectionFragment = gql`
    fragment BlogArticleConnectionFragment on BlogArticleConnection {
  __typename
  edges {
    __typename
    node {
      ...ListedBlogArticleFragment
    }
  }
}
    ${ListedBlogArticleFragment}`;