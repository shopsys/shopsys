import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../../images/fragments/ImageFragment.generated';
import { BreadcrumbFragment } from '../../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { HreflangLinksFragment } from '../../../hreflangLinks/fragments/HreflangLinksFragment.generated';
export type TypeBlogArticleDetailFragment = { __typename: 'BlogArticle', id: number, uuid: string, name: string, slug: string, link: string, text: string | null, publishDate: any, seoTitle: string | null, seoMetaDescription: string | null, seoH1: string | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> };


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    
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
}
    ${ImageFragment}
${BreadcrumbFragment}
${HreflangLinksFragment}`;