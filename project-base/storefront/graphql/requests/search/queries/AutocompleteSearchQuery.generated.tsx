import * as Types from '../../types';

import gql from 'graphql-tag';
import { SimpleArticleInterfaceFragmentApi } from '../../articlesInterface/fragments/SimpleArticleInterfaceFragment.generated';
import { SimpleBrandFragmentApi } from '../../brands/fragments/SimpleBrandFragment.generated';
import { SimpleCategoryConnectionFragmentApi } from '../../categories/fragments/SimpleCategoryConnectionFragment.generated';
import { ProductFilterOptionsFragmentApi } from '../../productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { ListedProductConnectionFragmentApi } from '../../products/fragments/ListedProductConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type AutocompleteSearchQueryVariablesApi = Types.Exact<{
    search: Types.Scalars['String']['input'];
    maxProductCount: Types.InputMaybe<Types.Scalars['Int']['input']>;
    maxCategoryCount: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;

export type AutocompleteSearchQueryApi = {
    __typename?: 'Query';
    articlesSearch: Array<
        | { __typename: 'ArticleSite'; uuid: string; name: string; slug: string; placement: string; external: boolean }
        | {
              __typename: 'BlogArticle';
              name: string;
              slug: string;
              mainImage: {
                  __typename: 'Image';
                  name: string | null;
                  sizes: Array<{
                      __typename: 'ImageSize';
                      size: string;
                      url: string;
                      width: number | null;
                      height: number | null;
                      additionalSizes: Array<{
                          __typename: 'AdditionalSize';
                          height: number | null;
                          media: string;
                          url: string;
                          width: number | null;
                      }>;
                  }>;
              } | null;
          }
    >;
    brandSearch: Array<{ __typename: 'Brand'; name: string; slug: string }>;
    categoriesSearch: {
        __typename: 'CategoryConnection';
        totalCount: number;
        edges: Array<{
            __typename: 'CategoryEdge';
            node: { __typename: 'Category'; uuid: string; name: string; slug: string } | null;
        } | null> | null;
    };
    productsSearch: {
        __typename: 'ProductConnection';
        orderingMode: Types.ProductOrderingModeEnumApi;
        defaultOrderingMode: Types.ProductOrderingModeEnumApi | null;
        totalCount: number;
        productFilterOptions: {
            __typename: 'ProductFilterOptions';
            minimalPrice: string;
            maximalPrice: string;
            inStock: number;
            brands: Array<{
                __typename: 'BrandFilterOption';
                count: number;
                brand: { __typename: 'Brand'; uuid: string; name: string };
            }> | null;
            flags: Array<{
                __typename: 'FlagFilterOption';
                count: number;
                isSelected: boolean;
                flag: { __typename: 'Flag'; uuid: string; name: string; rgbColor: string };
            }> | null;
            parameters: Array<
                | {
                      __typename: 'ParameterCheckboxFilterOption';
                      name: string;
                      uuid: string;
                      isCollapsed: boolean;
                      values: Array<{
                          __typename: 'ParameterValueFilterOption';
                          uuid: string;
                          text: string;
                          count: number;
                          isSelected: boolean;
                      }>;
                  }
                | {
                      __typename: 'ParameterColorFilterOption';
                      name: string;
                      uuid: string;
                      isCollapsed: boolean;
                      values: Array<{
                          __typename: 'ParameterValueColorFilterOption';
                          uuid: string;
                          text: string;
                          count: number;
                          rgbHex: string | null;
                          isSelected: boolean;
                      }>;
                  }
                | {
                      __typename: 'ParameterSliderFilterOption';
                      name: string;
                      uuid: string;
                      minimalValue: number;
                      maximalValue: number;
                      isCollapsed: boolean;
                      selectedValue: number | null;
                      isSelectable: boolean;
                      unit: { __typename: 'Unit'; name: string } | null;
                  }
            > | null;
        };
        pageInfo: { __typename?: 'PageInfo'; hasNextPage: boolean };
        edges: Array<{
            __typename: 'ProductEdge';
            node:
                | {
                      __typename: 'MainVariant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      name: string;
                      stockQuantity: number;
                      isSellingDenied: boolean;
                      availableStoresCount: number;
                      exposedStoresCount: number;
                      catalogNumber: string;
                      isMainVariant: boolean;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'RegularProduct';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      name: string;
                      stockQuantity: number;
                      isSellingDenied: boolean;
                      availableStoresCount: number;
                      exposedStoresCount: number;
                      catalogNumber: string;
                      isMainVariant: boolean;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename: 'Category'; name: string }>;
                  }
                | {
                      __typename: 'Variant';
                      id: number;
                      uuid: string;
                      slug: string;
                      fullName: string;
                      name: string;
                      stockQuantity: number;
                      isSellingDenied: boolean;
                      availableStoresCount: number;
                      exposedStoresCount: number;
                      catalogNumber: string;
                      isMainVariant: boolean;
                      flags: Array<{ __typename: 'Flag'; uuid: string; name: string; rgbColor: string }>;
                      mainImage: {
                          __typename: 'Image';
                          name: string | null;
                          sizes: Array<{
                              __typename: 'ImageSize';
                              size: string;
                              url: string;
                              width: number | null;
                              height: number | null;
                              additionalSizes: Array<{
                                  __typename: 'AdditionalSize';
                                  height: number | null;
                                  media: string;
                                  url: string;
                                  width: number | null;
                              }>;
                          }>;
                      } | null;
                      price: {
                          __typename: 'ProductPrice';
                          priceWithVat: string;
                          priceWithoutVat: string;
                          vatAmount: string;
                          isPriceFrom: boolean;
                      };
                      availability: {
                          __typename: 'Availability';
                          name: string;
                          status: Types.AvailabilityStatusEnumApi;
                      };
                      brand: { __typename: 'Brand'; name: string; slug: string } | null;
                      categories: Array<{ __typename: 'Category'; name: string }>;
                  }
                | null;
        } | null> | null;
    };
};

export const AutocompleteSearchQueryDocumentApi = gql`
    query AutocompleteSearchQuery($search: String!, $maxProductCount: Int, $maxCategoryCount: Int) {
        articlesSearch(search: $search) {
            ...SimpleArticleInterfaceFragment
        }
        brandSearch(search: $search) {
            ...SimpleBrandFragment
        }
        categoriesSearch(search: $search, first: $maxCategoryCount) {
            ...SimpleCategoryConnectionFragment
        }
        productsSearch: products(search: $search, first: $maxProductCount) {
            orderingMode
            defaultOrderingMode
            totalCount
            productFilterOptions {
                ...ProductFilterOptionsFragment
            }
            ...ListedProductConnectionFragment
        }
    }
    ${SimpleArticleInterfaceFragmentApi}
    ${SimpleBrandFragmentApi}
    ${SimpleCategoryConnectionFragmentApi}
    ${ProductFilterOptionsFragmentApi}
    ${ListedProductConnectionFragmentApi}
`;

export function useAutocompleteSearchQueryApi(
    options: Omit<Urql.UseQueryArgs<AutocompleteSearchQueryVariablesApi>, 'query'>,
) {
    return Urql.useQuery<AutocompleteSearchQueryApi, AutocompleteSearchQueryVariablesApi>({
        query: AutocompleteSearchQueryDocumentApi,
        ...options,
    });
}

export interface PossibleTypesResultData {
    possibleTypes: {
        [key: string]: string[];
    };
}
const result: PossibleTypesResultData = {
    possibleTypes: {
        Advert: ['AdvertCode', 'AdvertImage'],
        ArticleInterface: ['ArticleSite', 'BlogArticle'],
        Breadcrumb: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
        CartInterface: ['Cart'],
        CustomerUser: ['CompanyCustomerUser', 'RegularCustomerUser'],
        NotBlogArticleInterface: ['ArticleLink', 'ArticleSite'],
        ParameterFilterOptionInterface: [
            'ParameterCheckboxFilterOption',
            'ParameterColorFilterOption',
            'ParameterSliderFilterOption',
        ],
        PriceInterface: ['Price', 'ProductPrice'],
        Product: ['MainVariant', 'RegularProduct', 'Variant'],
        ProductListable: ['Brand', 'Category', 'Flag'],
        Slug: [
            'ArticleSite',
            'BlogArticle',
            'BlogCategory',
            'Brand',
            'Category',
            'Flag',
            'MainVariant',
            'RegularProduct',
            'Store',
            'Variant',
        ],
    },
};
export default result;
