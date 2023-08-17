import * as Types from '../../types';

import gql from 'graphql-tag';
import { ImageSizesFragmentApi } from '../../images/fragments/ImageSizesFragment.generated';
import { PriceFragmentApi } from '../../prices/fragments/PriceFragment.generated';
export type ListedOrderFragmentApi = {
    __typename: 'Order';
    uuid: string;
    number: string;
    creationDate: any;
    productItems: Array<{ __typename: 'OrderItem'; quantity: number }>;
    transport: {
        __typename: 'Transport';
        name: string;
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
    };
    payment: { __typename: 'Payment'; name: string };
    totalPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
};

export const ListedOrderFragmentApi = gql`
    fragment ListedOrderFragment on Order {
        __typename
        uuid
        number
        creationDate
        productItems {
            __typename
            quantity
        }
        transport {
            __typename
            name
            mainImage {
                ...ImageSizesFragment
            }
        }
        payment {
            __typename
            name
        }
        totalPrice {
            ...PriceFragment
        }
    }
    ${ImageSizesFragmentApi}
    ${PriceFragmentApi}
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
