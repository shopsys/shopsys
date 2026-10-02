// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragment } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { SeoAttributesFragment } from '../../seo/fragments/SeoAttributesFragment.generated';
import { HreflangLinksFragment } from '../../hreflangLinks/fragments/HreflangLinksFragment.generated';
export type TypeBlogCategoryDetailFragment = { __typename: 'BlogCategory', uuid: string, name: string, description: string | null, articlesTotalCount: number, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, seo: { __typename: 'SeoAttributes', title: string | null, metaDescription: string | null, h1: string | null, metaRobots: string | null, canonicalUrl: string | null }, hreflangLinks: Array<{ hreflang: string, href: string }> };

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
  seo {
    ...SeoAttributesFragment
  }
  description
  hreflangLinks {
    ...HreflangLinksFragment
  }
  articlesTotalCount
}
    ${BreadcrumbFragment}
${ImageFragment}
${SeoAttributesFragment}
${HreflangLinksFragment}`;