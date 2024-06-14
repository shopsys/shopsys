import * as Types from '../../../types';

import gql from 'graphql-tag';
import { SimpleArticleSiteFragment } from '../articles/fragments/SimpleArticleSiteFragment.generated';
import { SimpleBlogArticleFragment } from '../blogArticles/fragments/SimpleBlogArticleFragment.generated';
export type TypeSimpleArticleInterfaceFragment_ArticleSite_ = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

export type TypeSimpleArticleInterfaceFragment_BlogArticle_ = { __typename: 'BlogArticle', name: string, slug: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null };

export type TypeSimpleArticleInterfaceFragment = TypeSimpleArticleInterfaceFragment_ArticleSite_ | TypeSimpleArticleInterfaceFragment_BlogArticle_;


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
    
export const SimpleArticleInterfaceFragment = gql`
    fragment SimpleArticleInterfaceFragment on ArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleBlogArticleFragment
}
    ${SimpleArticleSiteFragment}
${SimpleBlogArticleFragment}`;