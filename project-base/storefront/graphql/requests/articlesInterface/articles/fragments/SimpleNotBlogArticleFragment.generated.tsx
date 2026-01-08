// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { SimpleArticleSiteFragment } from './SimpleArticleSiteFragment.generated';
import { SimpleArticleLinkFragment } from './SimpleArticleLinkFragment.generated';
export type TypeSimpleNotBlogArticleFragment_ArticleLink_ = { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean };

export type TypeSimpleNotBlogArticleFragment_ArticleSite_ = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

export type TypeSimpleNotBlogArticleFragment = TypeSimpleNotBlogArticleFragment_ArticleLink_ | TypeSimpleNotBlogArticleFragment_ArticleSite_;

export const SimpleNotBlogArticleFragment = gql`
    fragment SimpleNotBlogArticleFragment on NotBlogArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleArticleLinkFragment
}
    ${SimpleArticleSiteFragment}
${SimpleArticleLinkFragment}`;