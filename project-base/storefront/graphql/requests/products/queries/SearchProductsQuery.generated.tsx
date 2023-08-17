import * as Types from '../../types';

import gql from 'graphql-tag';
import { ProductFilterOptionsFragmentApi } from '../../productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { ListedProductConnectionFragmentApi } from '../fragments/ListedProductConnectionFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type SearchProductsQueryVariablesApi = Types.Exact<{
    endCursor: Types.Scalars['String']['input'];
    orderingMode: Types.InputMaybe<Types.ProductOrderingModeEnumApi>;
    filter: Types.InputMaybe<Types.ProductFilterApi>;
    search: Types.Scalars['String']['input'];
    pageSize: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;

export type SearchProductsQueryApi = {
    __typename?: 'Query';
    products: {
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

export const SearchProductsQueryDocumentApi = gql`
    query SearchProductsQuery(
        $endCursor: String!
        $orderingMode: ProductOrderingModeEnum
        $filter: ProductFilter
        $search: String!
        $pageSize: Int
    ) {
        products(after: $endCursor, orderingMode: $orderingMode, filter: $filter, search: $search, first: $pageSize) {
            orderingMode
            defaultOrderingMode
            totalCount
            productFilterOptions {
                ...ProductFilterOptionsFragment
            }
            ...ListedProductConnectionFragment
        }
    }
    ${ProductFilterOptionsFragmentApi}
    ${ListedProductConnectionFragmentApi}
`;

export function useSearchProductsQueryApi(options: Omit<Urql.UseQueryArgs<SearchProductsQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<SearchProductsQueryApi, SearchProductsQueryVariablesApi>({
        query: SearchProductsQueryDocumentApi,
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
