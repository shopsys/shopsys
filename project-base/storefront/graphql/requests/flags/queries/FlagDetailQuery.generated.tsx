import * as Types from '../../../types';

import gql from 'graphql-tag';
import { FlagDetailFragment } from '../fragments/FlagDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeFlagDetailQueryVariables = Types.Exact<{
  urlSlug: Types.InputMaybe<Types.Scalars['String']['input']>;
  orderingMode: Types.InputMaybe<Types.TypeProductOrderingModeEnum>;
  filter: Types.InputMaybe<Types.TypeProductFilter>;
}>;


export type TypeFlagDetailQuery = { __typename?: 'Query', flag: { __typename: 'Flag', uuid: string, slug: string, name: string, breadcrumb: Array<{ __typename: 'Link', name: string, slug: string }>, products: { __typename: 'ProductConnection', orderingMode: Types.TypeProductOrderingModeEnum, defaultOrderingMode: Types.TypeProductOrderingModeEnum | null, totalCount: number, productFilterOptions: { __typename: 'ProductFilterOptions', minimalPrice: string, maximalPrice: string, inStock: number, brands: Array<{ __typename: 'BrandFilterOption', count: number, brand: { __typename: 'Brand', uuid: string, name: string } }> | null, flags: Array<{ __typename: 'FlagFilterOption', count: number, isSelected: boolean, flag: { __typename: 'Flag', uuid: string, name: string, rgbColor: string } }> | null, parameters: Array<{ __typename: 'ParameterCheckboxFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueFilterOption', uuid: string, text: string, count: number, isSelected: boolean }> } | { __typename: 'ParameterColorFilterOption', name: string, uuid: string, isCollapsed: boolean, values: Array<{ __typename: 'ParameterValueColorFilterOption', uuid: string, text: string, count: number, rgbHex: string | null, isSelected: boolean }> } | { __typename: 'ParameterSliderFilterOption', name: string, uuid: string, minimalValue: number, maximalValue: number, isCollapsed: boolean, selectedValue: number | null, isSelectable: boolean, unit: { __typename: 'Unit', name: string } | null }> | null } }, hreflangLinks: Array<{ __typename?: 'HreflangLink', hreflang: string, href: string }> } | null };


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
    

export const FlagDetailQueryDocument = gql`
    query FlagDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) @friendlyUrl {
  flag(urlSlug: $urlSlug) {
    ...FlagDetailFragment
  }
}
    ${FlagDetailFragment}`;

export function useFlagDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeFlagDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeFlagDetailQuery, TypeFlagDetailQueryVariables>({ query: FlagDetailQueryDocument, ...options });
};