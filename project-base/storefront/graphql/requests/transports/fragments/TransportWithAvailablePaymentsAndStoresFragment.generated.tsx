import * as Types from '../../types';

import gql from 'graphql-tag';
import { PriceFragmentApi } from '../../prices/fragments/PriceFragment.generated';
import { ImageSizesFragmentApi } from '../../images/fragments/ImageSizesFragment.generated';
import { SimplePaymentFragmentApi } from '../../payments/fragments/SimplePaymentFragment.generated';
import { ListedStoreConnectionFragmentApi } from '../../stores/fragments/ListedStoreConnectionFragment.generated';
export type TransportWithAvailablePaymentsAndStoresFragmentApi = {
    __typename: 'Transport';
    uuid: string;
    name: string;
    description: string | null;
    instruction: string | null;
    daysUntilDelivery: number;
    isPersonalPickup: boolean;
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
    payments: Array<{
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
    }>;
    stores: {
        __typename: 'StoreConnection';
        edges: Array<{
            __typename: 'StoreEdge';
            node: {
                __typename: 'Store';
                slug: string;
                name: string;
                description: string | null;
                locationLatitude: string | null;
                locationLongitude: string | null;
                street: string;
                postcode: string;
                city: string;
                identifier: string;
                openingHours: {
                    __typename?: 'OpeningHours';
                    isOpen: boolean;
                    dayOfWeek: number;
                    openingHoursOfDays: Array<{
                        __typename?: 'OpeningHoursOfDay';
                        dayOfWeek: number;
                        firstOpeningTime: string | null;
                        firstClosingTime: string | null;
                        secondOpeningTime: string | null;
                        secondClosingTime: string | null;
                    }>;
                };
                country: { __typename: 'Country'; name: string; code: string };
            } | null;
        } | null> | null;
    } | null;
    transportType: { __typename: 'TransportType'; code: string };
};

export const TransportWithAvailablePaymentsAndStoresFragmentApi = gql`
    fragment TransportWithAvailablePaymentsAndStoresFragment on Transport {
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
        payments {
            ...SimplePaymentFragment
        }
        daysUntilDelivery
        stores {
            ...ListedStoreConnectionFragment
        }
        transportType {
            __typename
            code
        }
        isPersonalPickup
    }
    ${PriceFragmentApi}
    ${ImageSizesFragmentApi}
    ${SimplePaymentFragmentApi}
    ${ListedStoreConnectionFragmentApi}
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
