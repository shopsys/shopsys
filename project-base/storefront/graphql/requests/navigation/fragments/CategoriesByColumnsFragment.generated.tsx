import * as Types from '../../types';

import gql from 'graphql-tag';
import { ColumnCategoriesFragmentApi } from './ColumnCategoriesFragment.generated';
export type CategoriesByColumnFragmentApi = {
    __typename: 'NavigationItem';
    name: string;
    link: string;
    categoriesByColumns: Array<{
        __typename: 'NavigationItemCategoriesByColumns';
        columnNumber: number;
        categories: Array<{
            __typename: 'Category';
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
            children: Array<{ __typename: 'Category'; name: string; slug: string }>;
        }>;
    }>;
};

export const CategoriesByColumnFragmentApi = gql`
    fragment CategoriesByColumnFragment on NavigationItem {
        __typename
        name
        link
        categoriesByColumns {
            ...ColumnCategoriesFragment
        }
    }
    ${ColumnCategoriesFragmentApi}
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
