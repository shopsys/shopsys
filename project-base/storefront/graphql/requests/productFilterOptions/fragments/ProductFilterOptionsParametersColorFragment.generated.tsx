import * as Types from '../../types';

import gql from 'graphql-tag';
export type ProductFilterOptionsParametersColorFragmentApi = {
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
};

export const ProductFilterOptionsParametersColorFragmentApi = gql`
    fragment ProductFilterOptionsParametersColorFragment on ParameterColorFilterOption {
        name
        uuid
        __typename
        values {
            __typename
            uuid
            text
            count
            rgbHex
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
