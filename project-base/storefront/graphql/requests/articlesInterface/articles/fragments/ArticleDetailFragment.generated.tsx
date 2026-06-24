// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragment } from '../../../breadcrumbs/fragments/BreadcrumbFragment.generated';
export type TypeArticleDetailFragment = { __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: string | null, seoTitle: string | null, seoMetaDescription: string | null, createdAt: string, seoH1: string | null, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }> };

export const ArticleDetailFragment = gql`
    fragment ArticleDetailFragment on ArticleSite {
  __typename
  uuid
  slug
  placement
  articleName: name
  text
  breadcrumb {
    ...BreadcrumbFragment
  }
  seoTitle
  seoMetaDescription
  createdAt
  seoH1
}
    ${BreadcrumbFragment}`;