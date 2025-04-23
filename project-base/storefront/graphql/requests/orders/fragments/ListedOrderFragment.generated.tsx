import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
export type TypeListedOrderFragment = { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number, product: { __typename: 'MainVariant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean } | { __typename: 'RegularProduct', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean } | { __typename: 'Variant', isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean } | null }>, transport: { __typename: 'Transport', name: string, mainImage: { __typename: 'Image', url: string, name: string | null } | null }, payment: { __typename: 'Payment', name: string, type: Types.TypePaymentTypeEnum, mainImage: { __typename?: 'Image', url: string } | null }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };


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
    
export const ListedOrderFragment = gql`
    fragment ListedOrderFragment on Order {
  __typename
  uuid
  number
  creationDate
  productItems {
    __typename
    quantity
    product {
      __typename
      isVisible
      isSellingDenied
      isInquiryType
    }
  }
  transport {
    __typename
    name
    mainImage {
      ...ImageFragment
    }
    mainImage {
      url
    }
  }
  payment {
    __typename
    name
    type
    mainImage {
      url
    }
  }
  totalPrice {
    ...PriceFragment
  }
  isPaid
  hasExternalPayment
  hasPaymentInProcess
  status
  note
}
    ${ImageFragment}
${PriceFragment}`;