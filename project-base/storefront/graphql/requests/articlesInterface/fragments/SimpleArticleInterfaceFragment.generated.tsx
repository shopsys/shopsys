// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleArticleSiteFragment } from '../articles/fragments/SimpleArticleSiteFragment.generated';
import { SimpleBlogArticleFragment } from '../blogArticles/fragments/SimpleBlogArticleFragment.generated';
export type TypeSimpleArticleInterfaceFragment_ArticleSite = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

export type TypeSimpleArticleInterfaceFragment_BlogArticle = { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export type TypeSimpleArticleInterfaceFragment =
  | TypeSimpleArticleInterfaceFragment_ArticleSite
  | TypeSimpleArticleInterfaceFragment_BlogArticle
;

export const SimpleArticleInterfaceFragment = gql`
    fragment SimpleArticleInterfaceFragment on ArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleBlogArticleFragment
}
    ${SimpleArticleSiteFragment}
${SimpleBlogArticleFragment}`;