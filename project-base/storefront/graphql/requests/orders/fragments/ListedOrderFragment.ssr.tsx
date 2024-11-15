import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.ssr';
import { PriceFragment } from '../../prices/fragments/PriceFragment.ssr';
export type TypeListedOrderFragment = { __typename: 'Order', uuid: string, number: string, creationDate: any, isPaid: boolean, status: string, note: string | null, productItems: Array<{ __typename: 'OrderItem', quantity: number, product: { __typename: 'MainVariant', isVisible: boolean, isSellingDenied: boolean } | { __typename: 'RegularProduct', isVisible: boolean, isSellingDenied: boolean } | { __typename: 'Variant', isVisible: boolean, isSellingDenied: boolean } | null }>, transport: { __typename: 'Transport', name: string, mainImage: { __typename: 'Image', url: string, name: string | null } | null }, payment: { __typename: 'Payment', name: string, type: string, mainImage: { __typename?: 'Image', url: string } | null }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } };


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
  status
  note
}
    ${ImageFragment}
${PriceFragment}`;