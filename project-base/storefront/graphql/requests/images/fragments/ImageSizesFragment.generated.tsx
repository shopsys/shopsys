import * as Types from '../../types';

import gql from 'graphql-tag';
import { ImageSizeFragmentApi } from './ImageSizeFragment.generated';
export type ImageSizesFragmentApi = {
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
};

export const ImageSizesFragmentApi = gql`
    fragment ImageSizesFragment on Image {
        __typename
        name
        sizes {
            ...ImageSizeFragment
        }
    }
    ${ImageSizeFragmentApi}
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
