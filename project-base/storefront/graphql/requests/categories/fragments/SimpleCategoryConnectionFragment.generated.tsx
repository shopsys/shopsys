import * as Types from '../../types';

import gql from 'graphql-tag';
import { SimpleCategoryFragmentApi } from './SimpleCategoryFragment.generated';
export type SimpleCategoryConnectionFragmentApi = {
    __typename: 'CategoryConnection';
    totalCount: number;
    edges: Array<{
        __typename: 'CategoryEdge';
        node: { __typename: 'Category'; uuid: string; name: string; slug: string } | null;
    } | null> | null;
};

export const SimpleCategoryConnectionFragmentApi = gql`
    fragment SimpleCategoryConnectionFragment on CategoryConnection {
        __typename
        totalCount
        edges {
            __typename
            node {
                ...SimpleCategoryFragment
            }
        }
    }
    ${SimpleCategoryFragmentApi}
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
