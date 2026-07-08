// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragment } from '../../../breadcrumbs/fragments/BreadcrumbFragment.generated';
export type TypeArticleDetailFragment = (
  { __typename: 'ArticleSite' }
  & Pick<Types.TypeArticleSite, 'uuid' | 'slug' | 'placement' | 'text' | 'seoTitle' | 'seoMetaDescription' | 'createdAt' | 'seoH1'>
  & { articleName: Types.TypeArticleSite['name'] }
  & { breadcrumb: Array<(
    { __typename: 'Link' }
    & Pick<Types.TypeLink, 'name' | 'slug'>
  )> }
);

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