// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { SimpleArticleSiteFragment } from './SimpleArticleSiteFragment.generated';
import { SimpleArticleLinkFragment } from './SimpleArticleLinkFragment.generated';
export type TypeSimpleNotBlogArticleFragment_ArticleLink_ = (
  { __typename: 'ArticleLink' }
  & Pick<Types.TypeArticleLink, 'uuid' | 'name' | 'url' | 'placement' | 'external'>
);

export type TypeSimpleNotBlogArticleFragment_ArticleSite_ = (
  { __typename: 'ArticleSite' }
  & Pick<Types.TypeArticleSite, 'uuid' | 'name' | 'slug' | 'placement' | 'external'>
);

export type TypeSimpleNotBlogArticleFragment = TypeSimpleNotBlogArticleFragment_ArticleLink_ | TypeSimpleNotBlogArticleFragment_ArticleSite_;

export const SimpleNotBlogArticleFragment = gql`
    fragment SimpleNotBlogArticleFragment on NotBlogArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleArticleLinkFragment
}
    ${SimpleArticleSiteFragment}
${SimpleArticleLinkFragment}`;