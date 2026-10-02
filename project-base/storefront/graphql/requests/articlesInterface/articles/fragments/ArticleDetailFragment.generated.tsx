// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragment } from '../../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { SeoAttributesFragment } from '../../../seo/fragments/SeoAttributesFragment.generated';
export type TypeArticleDetailFragment = { __typename: 'ArticleSite', uuid: string, slug: string, placement: string, text: string | null, createdAt: string, articleName: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, seo: { __typename: 'SeoAttributes', title: string | null, metaDescription: string | null, h1: string | null, metaRobots: string | null, canonicalUrl: string | null } };

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
  createdAt
  seo {
    ...SeoAttributesFragment
  }
}
    ${BreadcrumbFragment}
${SeoAttributesFragment}`;