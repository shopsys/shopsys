// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { SimpleArticleSiteFragment } from './SimpleArticleSiteFragment.generated';
import { SimpleArticleLinkFragment } from './SimpleArticleLinkFragment.generated';
export type TypeSimpleNotBlogArticleFragment_ArticleLink = { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean };

export type TypeSimpleNotBlogArticleFragment_ArticleSite = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

export type TypeSimpleNotBlogArticleFragment =
  | TypeSimpleNotBlogArticleFragment_ArticleLink
  | TypeSimpleNotBlogArticleFragment_ArticleSite
;

export const SimpleNotBlogArticleFragment = gql`
    fragment SimpleNotBlogArticleFragment on NotBlogArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleArticleLinkFragment
}
    ${SimpleArticleSiteFragment}
${SimpleArticleLinkFragment}`;