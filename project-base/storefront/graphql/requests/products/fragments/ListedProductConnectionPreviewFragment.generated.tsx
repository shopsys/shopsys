import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ProductFilterOptionsFragment } from '../../productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
export type ListedProductConnectionPreviewFragment = { __typename: 'ProductConnection', orderingMode: Types.ProductOrderingModeEnum, defaultOrderingMode: Types.ProductOrderingModeEnum | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } };


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
    
export const ListedProductConnectionPreviewFragment = gql`
    fragment ListedProductConnectionPreviewFragment on ProductConnection {
  __typename
  orderingMode
  defaultOrderingMode
  productFilterOptions {
    ...ProductFilterOptionsFragment
  }
  totalCount
}
    ${ProductFilterOptionsFragment}`;