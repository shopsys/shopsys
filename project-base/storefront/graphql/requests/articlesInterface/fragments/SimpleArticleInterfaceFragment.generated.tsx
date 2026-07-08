// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleArticleSiteFragment } from '../articles/fragments/SimpleArticleSiteFragment.generated';
import { SimpleBlogArticleFragment } from '../blogArticles/fragments/SimpleBlogArticleFragment.generated';
export type TypeSimpleArticleInterfaceFragment_ArticleSite_ = (
  { __typename: 'ArticleSite' }
  & Pick<Types.TypeArticleSite, 'uuid' | 'name' | 'slug' | 'placement' | 'external'>
);

export type TypeSimpleArticleInterfaceFragment_BlogArticle_ = (
  { __typename: 'BlogArticle' }
  & Pick<Types.TypeBlogArticle, 'name' | 'slug'>
  & { mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )> }
);

export type TypeSimpleArticleInterfaceFragment = TypeSimpleArticleInterfaceFragment_ArticleSite_ | TypeSimpleArticleInterfaceFragment_BlogArticle_;

export const SimpleArticleInterfaceFragment = gql`
    fragment SimpleArticleInterfaceFragment on ArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleBlogArticleFragment
}
    ${SimpleArticleSiteFragment}
${SimpleBlogArticleFragment}`;