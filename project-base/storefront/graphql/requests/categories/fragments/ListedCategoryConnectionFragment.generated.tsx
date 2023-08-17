import * as Types from '../../types';

import gql from 'graphql-tag';
import { ListedCategoryFragmentApi } from './ListedCategoryFragment.generated';
export type ListedCategoryConnectionFragmentApi = {
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

export const ListedCategoryConnectionFragmentApi = gql`
    fragment ListedCategoryConnectionFragment on CategoryConnection {
        __typename
        totalCount
        edges {
            __typename
            node {
                ...ListedCategoryFragment
            }
        }
    }
    ${ListedCategoryFragmentApi}
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
