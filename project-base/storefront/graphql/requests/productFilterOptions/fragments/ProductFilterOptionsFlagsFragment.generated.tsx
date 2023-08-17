import * as Types from '../../types';

import gql from 'graphql-tag';
import { SimpleFlagFragmentApi } from '../../flags/fragments/SimpleFlagFragment.generated';
export type ProductFilterOptionsFlagsFragmentApi = {
    __typename: 'FlagFilterOption';
    count: number;
    isSelected: boolean;
    flag: { __typename: 'Flag'; uuid: string; name: string; rgbColor: string };
};

export const ProductFilterOptionsFlagsFragmentApi = gql`
    fragment ProductFilterOptionsFlagsFragment on FlagFilterOption {
        __typename
        count
        flag {
            ...SimpleFlagFragment
        }
        isSelected
    }
    ${SimpleFlagFragmentApi}
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
