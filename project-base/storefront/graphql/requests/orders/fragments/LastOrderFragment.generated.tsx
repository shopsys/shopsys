import * as Types from '../../types';

import gql from 'graphql-tag';
import { SimpleTransportFragmentApi } from '../../transports/fragments/SimpleTransportFragment.generated';
import { SimplePaymentFragmentApi } from '../../payments/fragments/SimplePaymentFragment.generated';
import { CountryFragmentApi } from '../../countries/fragments/CountryFragment.generated';
export type LastOrderFragmentApi = {
    __typename: 'Order';
    pickupPlaceIdentifier: string | null;
    deliveryStreet: string | null;
    deliveryCity: string | null;
    deliveryPostcode: string | null;
    transport: { __typename: 'Transport'; uuid: string; name: string; description: string | null };
    payment: {
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
    deliveryCountry: { __typename: 'Country'; name: string; code: string } | null;
};

export const LastOrderFragmentApi = gql`
    fragment LastOrderFragment on Order {
        __typename
        transport {
            ...SimpleTransportFragment
        }
        payment {
            ...SimplePaymentFragment
        }
        pickupPlaceIdentifier
        deliveryStreet
        deliveryCity
        deliveryPostcode
        deliveryCountry {
            ...CountryFragment
        }
    }
    ${SimpleTransportFragmentApi}
    ${SimplePaymentFragmentApi}
    ${CountryFragmentApi}
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
