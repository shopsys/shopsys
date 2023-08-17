import * as Types from '../../types';

import gql from 'graphql-tag';
import { BreadcrumbFragmentApi } from '../../breadcrumbs/fragments/BreadcrumbFragment.generated';
import { ListedProductConnectionPreviewFragmentApi } from '../../products/fragments/ListedProductConnectionPreviewFragment.generated';
export type FlagDetailFragmentApi = {
    __typename: 'Flag';
    uuid: string;
    slug: string;
    name: string;
    breadcrumb: Array<{ __typename: 'Link'; name: string; slug: string }>;
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
};

export const FlagDetailFragmentApi = gql`
    fragment FlagDetailFragment on Flag {
        __typename
        uuid
        slug
        breadcrumb {
            ...BreadcrumbFragment
        }
        name
        products(orderingMode: $orderingMode, filter: $filter) {
            ...ListedProductConnectionPreviewFragment
        }
    }
    ${BreadcrumbFragmentApi}
    ${ListedProductConnectionPreviewFragmentApi}
`;

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
