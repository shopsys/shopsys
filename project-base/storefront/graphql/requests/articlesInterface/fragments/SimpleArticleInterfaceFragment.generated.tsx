// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleArticleSiteFragment } from '../articles/fragments/SimpleArticleSiteFragment.generated';
import { SimpleBlogArticleFragment } from '../blogArticles/fragments/SimpleBlogArticleFragment.generated';
export type TypeSimpleArticleInterfaceFragment_ArticleSite_ = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

export type TypeSimpleArticleInterfaceFragment_BlogArticle_ = { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export type TypeSimpleArticleInterfaceFragment = TypeSimpleArticleInterfaceFragment_ArticleSite_ | TypeSimpleArticleInterfaceFragment_BlogArticle_;

export const SimpleArticleInterfaceFragment = gql`
    fragment SimpleArticleInterfaceFragment on ArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleBlogArticleFragment
}
    ${SimpleArticleSiteFragment}
${SimpleBlogArticleFragment}`;