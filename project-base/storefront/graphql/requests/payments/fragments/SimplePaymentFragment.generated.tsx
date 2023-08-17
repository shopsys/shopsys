import * as Types from '../../types';

import gql from 'graphql-tag';
import { PriceFragmentApi } from '../../prices/fragments/PriceFragment.generated';
import { ImageSizesFragmentApi } from '../../images/fragments/ImageSizesFragment.generated';
export type SimplePaymentFragmentApi = {
    __typename: 'Payment';
    uuid: string;
    name: string;
    description: string | null;
    instruction: string | null;
    type: string;
    price: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
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
    goPayPaymentMethod: {
        __typename: 'GoPayPaymentMethod';
        identifier: string;
        name: string;
        paymentGroup: string;
    } | null;
};

export const SimplePaymentFragmentApi = gql`
    fragment SimplePaymentFragment on Payment {
        __typename
        uuid
        name
        description
        instruction
        price {
            ...PriceFragment
        }
        mainImage {
            ...ImageSizesFragment
        }
        type
        goPayPaymentMethod {
            __typename
            identifier
            name
            paymentGroup
        }
    }
    ${PriceFragmentApi}
    ${ImageSizesFragmentApi}
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
