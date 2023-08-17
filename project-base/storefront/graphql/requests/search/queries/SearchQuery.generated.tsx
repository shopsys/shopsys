import * as Types from '../../types';

import gql from 'graphql-tag';
import { SimpleArticleInterfaceFragmentApi } from '../../articlesInterface/fragments/SimpleArticleInterfaceFragment.generated';
import { ListedBrandFragmentApi } from '../../brands/fragments/ListedBrandFragment.generated';
import { ListedCategoryConnectionFragmentApi } from '../../categories/fragments/ListedCategoryConnectionFragment.generated';
import { ListedProductConnectionPreviewFragmentApi } from '../../products/fragments/ListedProductConnectionPreviewFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type SearchQueryVariablesApi = Types.Exact<{
    search: Types.Scalars['String']['input'];
    orderingMode: Types.InputMaybe<Types.ProductOrderingModeEnumApi>;
    filter: Types.InputMaybe<Types.ProductFilterApi>;
    pageSize: Types.InputMaybe<Types.Scalars['Int']['input']>;
}>;

export type SearchQueryApi = {
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
    brandSearch: Array<{
        __typename: 'Brand';
        uuid: string;
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
    }>;
    categoriesSearch: {
        __typename: 'CategoryConnection';
        totalCount: number;
        edges: Array<{
            __typename: 'CategoryEdge';
            node: {
                __typename: 'Category';
                uuid: string;
                name: string;
                slug: string;
                products: { __typename: 'ProductConnection'; totalCount: number };
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
            } | null;
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
    };
};

export const SearchQueryDocumentApi = gql`
    query SearchQuery(
        $search: String!
        $orderingMode: ProductOrderingModeEnum
        $filter: ProductFilter
        $pageSize: Int
    ) {
        articlesSearch(search: $search) {
            ...SimpleArticleInterfaceFragment
        }
        brandSearch(search: $search) {
            ...ListedBrandFragment
        }
        categoriesSearch(search: $search) {
            ...ListedCategoryConnectionFragment
        }
        productsSearch: products(search: $search, orderingMode: $orderingMode, filter: $filter, first: $pageSize) {
            ...ListedProductConnectionPreviewFragment
        }
    }
    ${SimpleArticleInterfaceFragmentApi}
    ${ListedBrandFragmentApi}
    ${ListedCategoryConnectionFragmentApi}
    ${ListedProductConnectionPreviewFragmentApi}
`;

export function useSearchQueryApi(options: Omit<Urql.UseQueryArgs<SearchQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<SearchQueryApi, SearchQueryVariablesApi>({ query: SearchQueryDocumentApi, ...options });
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
