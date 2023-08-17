import * as Types from '../../types';

import gql from 'graphql-tag';
import { CategoryDetailFragmentApi } from '../fragments/CategoryDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type CategoryDetailQueryVariablesApi = Types.Exact<{
    urlSlug: Types.InputMaybe<Types.Scalars['String']['input']>;
    orderingMode: Types.InputMaybe<Types.ProductOrderingModeEnumApi>;
    filter: Types.InputMaybe<Types.ProductFilterApi>;
}>;

export type CategoryDetailQueryApi = {
    __typename?: 'Query';
    category: {
        __typename: 'Category';
        id: number;
        uuid: string;
        slug: string;
        originalCategorySlug: string | null;
        name: string;
        description: string | null;
        seoH1: string | null;
        seoTitle: string | null;
        seoMetaDescription: string | null;
        breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
        children: Array<{
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
        }>;
        linkedCategories: Array<{
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
        }>;
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
        };
        readyCategorySeoMixLinks: Array<{ __typename: 'Link'; name: string; slug: string }>;
    } | null;
};

export const CategoryDetailQueryDocumentApi = gql`
    query CategoryDetailQuery($urlSlug: String, $orderingMode: ProductOrderingModeEnum, $filter: ProductFilter) {
        category(urlSlug: $urlSlug, orderingMode: $orderingMode, filter: $filter) {
            ...CategoryDetailFragment
        }
    }
    ${CategoryDetailFragmentApi}
`;

export function useCategoryDetailQueryApi(options?: Omit<Urql.UseQueryArgs<CategoryDetailQueryVariablesApi>, 'query'>) {
    return Urql.useQuery<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>({
        query: CategoryDetailQueryDocumentApi,
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
