import * as Types from '../../types';

import gql from 'graphql-tag';
import { CountryFragmentApi } from '../../countries/fragments/CountryFragment.generated';
import { SimplePaymentFragmentApi } from '../../payments/fragments/SimplePaymentFragment.generated';
import { SimpleTransportFragmentApi } from '../../transports/fragments/SimpleTransportFragment.generated';
import { OrderDetailItemFragmentApi } from '../../orders/fragments/OrderDetailItemFragment.generated';
import { CustomerUserFragmentApi } from '../../customer/fragments/CustomerUserFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type PersonalDataDetailQueryVariablesApi = Types.Exact<{
    hash: Types.Scalars['String']['input'];
}>;

export type PersonalDataDetailQueryApi = {
    __typename?: 'Query';
    accessPersonalData: {
        __typename: 'PersonalData';
        exportLink: string;
        orders: Array<{
            __typename: 'Order';
            uuid: string;
            city: string;
            companyName: string | null;
            number: string;
            creationDate: any;
            firstName: string | null;
            lastName: string | null;
            telephone: string;
            companyNumber: string | null;
            companyTaxNumber: string | null;
            street: string;
            postcode: string;
            deliveryFirstName: string | null;
            deliveryLastName: string | null;
            deliveryCompanyName: string | null;
            deliveryTelephone: string | null;
            deliveryStreet: string | null;
            deliveryCity: string | null;
            deliveryPostcode: string | null;
            country: { __typename: 'Country'; name: string; code: string };
            deliveryCountry: { __typename: 'Country'; name: string; code: string } | null;
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
            transport: { __typename: 'Transport'; uuid: string; name: string; description: string | null };
            productItems: Array<{
                __typename: 'OrderItem';
                name: string;
                vatRate: string;
                quantity: number;
                unit: string | null;
                unitPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
                totalPrice: { __typename: 'Price'; priceWithVat: string; priceWithoutVat: string; vatAmount: string };
            }>;
            totalPrice: { __typename?: 'Price'; priceWithVat: string };
        }>;
        customerUser:
            | {
                  __typename: 'CompanyCustomerUser';
                  companyName: string | null;
                  companyNumber: string | null;
                  companyTaxNumber: string | null;
                  uuid: string;
                  firstName: string;
                  lastName: string;
                  email: string;
                  telephone: string | null;
                  street: string;
                  city: string;
                  postcode: string;
                  newsletterSubscription: boolean;
                  pricingGroup: string;
                  country: { __typename: 'Country'; name: string; code: string };
                  defaultDeliveryAddress: {
                      __typename: 'DeliveryAddress';
                      uuid: string;
                      companyName: string | null;
                      street: string | null;
                      city: string | null;
                      postcode: string | null;
                      telephone: string | null;
                      firstName: string | null;
                      lastName: string | null;
                      country: { __typename: 'Country'; name: string; code: string } | null;
                  } | null;
                  deliveryAddresses: Array<{
                      __typename: 'DeliveryAddress';
                      uuid: string;
                      companyName: string | null;
                      street: string | null;
                      city: string | null;
                      postcode: string | null;
                      telephone: string | null;
                      firstName: string | null;
                      lastName: string | null;
                      country: { __typename: 'Country'; name: string; code: string } | null;
                  }>;
              }
            | {
                  __typename: 'RegularCustomerUser';
                  uuid: string;
                  firstName: string;
                  lastName: string;
                  email: string;
                  telephone: string | null;
                  street: string;
                  city: string;
                  postcode: string;
                  newsletterSubscription: boolean;
                  pricingGroup: string;
                  country: { __typename: 'Country'; name: string; code: string };
                  defaultDeliveryAddress: {
                      __typename: 'DeliveryAddress';
                      uuid: string;
                      companyName: string | null;
                      street: string | null;
                      city: string | null;
                      postcode: string | null;
                      telephone: string | null;
                      firstName: string | null;
                      lastName: string | null;
                      country: { __typename: 'Country'; name: string; code: string } | null;
                  } | null;
                  deliveryAddresses: Array<{
                      __typename: 'DeliveryAddress';
                      uuid: string;
                      companyName: string | null;
                      street: string | null;
                      city: string | null;
                      postcode: string | null;
                      telephone: string | null;
                      firstName: string | null;
                      lastName: string | null;
                      country: { __typename: 'Country'; name: string; code: string } | null;
                  }>;
              }
            | null;
        newsletterSubscriber: { __typename: 'NewsletterSubscriber'; email: string; createdAt: any } | null;
    };
};

export const PersonalDataDetailQueryDocumentApi = gql`
    query PersonalDataDetailQuery($hash: String!) {
        accessPersonalData(hash: $hash) {
            __typename
            orders {
                __typename
                uuid
                city
                companyName
                number
                creationDate
                firstName
                lastName
                telephone
                companyNumber
                companyTaxNumber
                street
                city
                postcode
                country {
                    ...CountryFragment
                }
                deliveryFirstName
                deliveryLastName
                deliveryCompanyName
                deliveryTelephone
                deliveryStreet
                deliveryCity
                deliveryPostcode
                deliveryCountry {
                    ...CountryFragment
                }
                payment {
                    ...SimplePaymentFragment
                }
                transport {
                    ...SimpleTransportFragment
                }
                productItems {
                    ...OrderDetailItemFragment
                }
                totalPrice {
                    priceWithVat
                }
            }
            customerUser {
                ...CustomerUserFragment
            }
            newsletterSubscriber {
                __typename
                email
                createdAt
            }
            exportLink
        }
    }
    ${CountryFragmentApi}
    ${SimplePaymentFragmentApi}
    ${SimpleTransportFragmentApi}
    ${OrderDetailItemFragmentApi}
    ${CustomerUserFragmentApi}
`;

export function usePersonalDataDetailQueryApi(
    options: Omit<Urql.UseQueryArgs<PersonalDataDetailQueryVariablesApi>, 'query'>,
) {
    return Urql.useQuery<PersonalDataDetailQueryApi, PersonalDataDetailQueryVariablesApi>({
        query: PersonalDataDetailQueryDocumentApi,
        ...options,
    });
}

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
