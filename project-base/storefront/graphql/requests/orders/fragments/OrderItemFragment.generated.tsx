import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
export type TypeOrderItemFragment = { __typename: 'OrderItem', quantity: number, product: { __typename: 'MainVariant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'RegularProduct', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | { __typename: 'Variant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, link: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null };


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
    "BaseCustomerUser": [
      "CompanyCustomerUser",
      "CurrentCompanyCustomerUser",
      "CurrentRegularCustomerUser",
      "RegularCustomerUser"
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
    "CurrentCustomerUser": [
      "CurrentCompanyCustomerUser",
      "CurrentRegularCustomerUser"
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
    
export const OrderItemFragment = gql`
    fragment OrderItemFragment on OrderItem {
  __typename
  quantity
  product {
    __typename
    isVisible
    isSellingDenied
    isInquiryType
    link
    mainImage {
      ...ImageFragment
    }
  }
}
    ${ImageFragment}`;