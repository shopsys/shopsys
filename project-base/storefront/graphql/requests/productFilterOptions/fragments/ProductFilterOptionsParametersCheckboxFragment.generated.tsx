import * as Types from '../../types';

import gql from 'graphql-tag';
export type ProductFilterOptionsParametersCheckboxFragmentApi = {
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
};

export const ProductFilterOptionsParametersCheckboxFragmentApi = gql`
    fragment ProductFilterOptionsParametersCheckboxFragment on ParameterCheckboxFilterOption {
        name
        uuid
        __typename
        values {
            __typename
            uuid
            text
            count
            isSelected
        }
        isCollapsed
    }
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
