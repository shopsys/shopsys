// @ts-nocheck
import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
import { BreadcrumbFragment } from '../../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { HreflangLinksFragment } from '../../../hreflangLinks/fragments/HreflangLinksFragment.generated';
import { SimpleBlogCategoryFragment } from '../../../blogCategories/fragments/SimpleBlogCategoryFragment.generated';
export type TypeBlogArticleDetailFragment = { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: any, seoTitle: string | null, seoMetaDescription: string | null, seoH1: string | null, mainBlogCategoryUuid: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }>, blogCategories: Array<{ __typename: 'BlogCategory', uuid: string, name: string, link: string, parent: { __typename?: 'BlogCategory', name: string } | null }> };

export const BlogArticleDetailFragment = gql`
    fragment BlogArticleDetailFragment on BlogArticle {
  __typename
  id
  uuid
  name
  slug
  link
  mainImage {
    ...ImageFragment
  }
  breadcrumb {
    ...BreadcrumbFragment
  }
  text
  publishDate
  seoTitle
  seoMetaDescription
  seoH1
  hreflangLinks {
    ...HreflangLinksFragment
  }
  mainBlogCategoryUuid
  blogCategories {
    ...SimpleBlogCategoryFragment
  }
}
    ${ImageFragment}
${BreadcrumbFragment}
${HreflangLinksFragment}
${SimpleBlogCategoryFragment}`;