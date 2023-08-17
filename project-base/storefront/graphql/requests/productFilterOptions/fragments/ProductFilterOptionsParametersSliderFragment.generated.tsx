import * as Types from '../../types';

import gql from 'graphql-tag';
export type ProductFilterOptionsParametersSliderFragmentApi = {
    __typename: 'ParameterSliderFilterOption';
    name: string;
    uuid: string;
    minimalValue: number;
    maximalValue: number;
    isCollapsed: boolean;
    selectedValue: number | null;
    isSelectable: boolean;
    unit: { __typename: 'Unit'; name: string } | null;
};

export const ProductFilterOptionsParametersSliderFragmentApi = gql`
    fragment ProductFilterOptionsParametersSliderFragment on ParameterSliderFilterOption {
        name
        uuid
        __typename
        minimalValue
        maximalValue
        unit {
            __typename
            name
        }
        isCollapsed
        selectedValue
        isSelectable
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
