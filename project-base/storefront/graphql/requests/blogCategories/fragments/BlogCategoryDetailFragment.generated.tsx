// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragment } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { HreflangLinksFragment } from '../../hreflangLinks/fragments/HreflangLinksFragment.generated';
export type TypeBlogCategoryDetailFragment = (
  { __typename: 'BlogCategory' }
  & Pick<Types.TypeBlogCategory, 'uuid' | 'name' | 'seoTitle' | 'seoMetaDescription' | 'description' | 'articlesTotalCount'>
  & { breadcrumb: Array<(
    { __typename: 'Link' }
    & Pick<Types.TypeLink, 'name' | 'slug'>
  )>, mainImage: Types.Maybe<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, hreflangLinks: Array<(
    { __typename?: 'HreflangLink' }
    & Pick<Types.TypeHreflangLink, 'hreflang' | 'href'>
  )> }
);

export const BlogCategoryDetailFragment = gql`
    fragment BlogCategoryDetailFragment on BlogCategory {
  __typename
  uuid
  name
  breadcrumb {
    ...BreadcrumbFragment
  }
  mainImage {
    ...ImageFragment
  }
  seoTitle
  seoMetaDescription
  description
  hreflangLinks {
    ...HreflangLinksFragment
  }
  articlesTotalCount
}
    ${BreadcrumbFragment}
${ImageFragment}
${HreflangLinksFragment}`;