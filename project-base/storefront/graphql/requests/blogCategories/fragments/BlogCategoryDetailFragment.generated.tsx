// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragment } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { HreflangLinksFragment } from '../../hreflangLinks/fragments/HreflangLinksFragment.generated';
export type TypeBlogCategoryDetailFragment = { __typename: 'BlogCategory', uuid: string, name: string, seoTitle: string | null, seoMetaDescription: string | null, description: string | null, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> };

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