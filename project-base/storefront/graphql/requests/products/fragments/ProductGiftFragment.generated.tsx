import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { ProductPriceFragment } from './ProductPriceFragment.generated';
export type TypeProductGiftFragment_MainVariant_ = { __typename?: 'MainVariant', name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } };

export type TypeProductGiftFragment_RegularProduct_ = { __typename?: 'RegularProduct', name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } };

export type TypeProductGiftFragment_Variant_ = { __typename?: 'Variant', name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } };

export type TypeProductGiftFragment = TypeProductGiftFragment_MainVariant_ | TypeProductGiftFragment_RegularProduct_ | TypeProductGiftFragment_Variant_;


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
    
export const ProductGiftFragment = gql`
    fragment ProductGiftFragment on Product {
  name
  images {
    ...ImageFragment
  }
  giftPrice {
    ...ProductPriceFragment
  }
}
    ${ImageFragment}
${ProductPriceFragment}`;