import * as Types from '../../types';

import gql from 'graphql-tag';
import { AdditionalSizeFragmentApi } from './AdditionalSizeFragment.generated';
export type ImageSizeFragmentApi = {
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
};

export const ImageSizeFragmentApi = gql`
    fragment ImageSizeFragment on ImageSize {
        __typename
        size
        url
        width
        height
        additionalSizes {
            ...AdditionalSizeFragment
        }
    }
    ${AdditionalSizeFragmentApi}
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
