import * as Types from '../../../../types';

import gql from 'graphql-tag';
import { SimpleArticleSiteFragment } from './SimpleArticleSiteFragment.generated';
import { SimpleArticleLinkFragment } from './SimpleArticleLinkFragment.generated';
export type SimpleNotBlogArticleFragment_ArticleLink_ = { __typename: 'ArticleLink', uuid: string, name: string, url: string, placement: string, external: boolean };

export type SimpleNotBlogArticleFragment_ArticleSite_ = { __typename: 'ArticleSite', uuid: string, name: string, slug: string, placement: string, external: boolean };

export type SimpleNotBlogArticleFragment = SimpleNotBlogArticleFragment_ArticleLink_ | SimpleNotBlogArticleFragment_ArticleSite_;


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
    "CartInterface": [
      "Cart"
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
    "PriceInterface": [
      "Price",
      "ProductPrice"
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
    
export const SimpleNotBlogArticleFragment = gql`
    fragment SimpleNotBlogArticleFragment on NotBlogArticleInterface {
  __typename
  ...SimpleArticleSiteFragment
  ...SimpleArticleLinkFragment
}
    ${SimpleArticleSiteFragment}
${SimpleArticleLinkFragment}`;