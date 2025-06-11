import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailItemFragment } from '../../orders/fragments/OrderDetailItemFragment.generated';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { SimplePaymentFragment } from '../../payments/fragments/SimplePaymentFragment.generated';
import { SimpleTransportFragment } from '../../transports/fragments/SimpleTransportFragment.generated';
import { BaseCustomerUserFragment } from '../../customer/fragments/BaseCustomerUserFragment.generated';
export type TypePersonalDataDetailFragment = { __typename: 'PersonalData', exportLink: string, orders: Array<{ __typename: 'Order', uuid: string, city: string, companyName: string | null, number: string, creationDate: any, firstName: string | null, lastName: string | null, telephone: string, companyNumber: string | null, companyTaxNumber: string | null, street: string, postcode: string, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, items: Array<{ __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any, customerUser: { __typename?: 'CompanyCustomerUser', uuid: string } | { __typename?: 'CurrentCompanyCustomerUser', uuid: string } | { __typename?: 'CurrentRegularCustomerUser', uuid: string } | { __typename?: 'RegularCustomerUser', uuid: string } | null }, product: { __typename?: 'MainVariant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'RegularProduct', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'Variant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | null }>, country: { __typename: 'Country', name: string, code: string }, deliveryCountry: { __typename: 'Country', name: string, code: string } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, transportTypeCode: Types.TypeTransportTypeEnum }, productItems: Array<{ __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any, customerUser: { __typename?: 'CompanyCustomerUser', uuid: string } | { __typename?: 'CurrentCompanyCustomerUser', uuid: string } | { __typename?: 'CurrentRegularCustomerUser', uuid: string } | { __typename?: 'RegularCustomerUser', uuid: string } | null }, product: { __typename?: 'MainVariant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'RegularProduct', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'Variant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | null }>, totalPrice: { __typename?: 'Price', priceWithVat: string } }>, customerUser: { __typename: 'CompanyCustomerUser', companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { __typename?: 'Image', url: string, name: string | null } | null } | null } | { __typename: 'CurrentCompanyCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { __typename?: 'Image', url: string, name: string | null } | null } | null } | { __typename: 'CurrentRegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { __typename?: 'Image', url: string, name: string | null } | null } | null } | { __typename: 'RegularCustomerUser', uuid: string, firstName: string | null, lastName: string | null, email: string, telephone: string | null, billingAddressUuid: string, street: string | null, city: string | null, postcode: string | null, newsletterSubscription: boolean, pricingGroup: string, hasPasswordSet: boolean, roles: Array<Types.TypeCustomerUserRoleEnum>, country: { __typename: 'Country', name: string, code: string } | null, defaultDeliveryAddress: { __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null } | null, deliveryAddresses: Array<{ __typename: 'DeliveryAddress', uuid: string, companyName: string | null, street: string | null, city: string | null, postcode: string | null, telephone: string | null, firstName: string | null, lastName: string | null, country: { __typename: 'Country', name: string, code: string } | null }>, roleGroup: { __typename: 'CustomerUserRoleGroup', uuid: string, name: string }, salesRepresentative: { __typename: 'SalesRepresentative', email: string | null, firstName: string | null, lastName: string | null, telephone: string | null, uuid: string, image: { __typename?: 'Image', url: string, name: string | null } | null } | null } | null, newsletterSubscriber: { __typename: 'NewsletterSubscriber', email: string, createdAt: any } | null, complaints: Array<{ __typename: 'Complaint', uuid: string, number: string, createdAt: any, status: string, deliveryFirstName: string, deliveryLastName: string, deliveryCompanyName: string | null, deliveryCity: string, deliveryPostcode: string, deliveryStreet: string, deliveryTelephone: string, deliveryCountry: { __typename?: 'Country', name: string }, items: Array<{ __typename: 'ComplaintItem', productName: string, quantity: number, description: string, orderItem: { __typename?: 'OrderItem', uuid: string } | null }> }> };


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "BaseCustomerUser": [
      "CompanyCustomerUser",
      "CurrentCompanyCustomerUser",
      "CurrentRegularCustomerUser",
      "RegularCustomerUser"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CurrentCustomerUser": [
      "CurrentCompanyCustomerUser",
      "CurrentRegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    
export const PersonalDataDetailFragment = gql`
    fragment PersonalDataDetailFragment on PersonalData {
  __typename
  orders {
    __typename
    uuid
    city
    companyName
    number
    creationDate
    items {
      ...OrderDetailItemFragment
    }
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
    ...BaseCustomerUserFragment
  }
  newsletterSubscriber {
    __typename
    email
    createdAt
  }
  exportLink
  complaints {
    __typename
    uuid
    number
    createdAt
    status
    deliveryFirstName
    deliveryLastName
    deliveryCompanyName
    deliveryCity
    deliveryPostcode
    deliveryStreet
    deliveryTelephone
    deliveryCountry {
      name
    }
    items {
      __typename
      productName
      quantity
      description
      orderItem {
        uuid
      }
    }
  }
}
    ${OrderDetailItemFragment}
${CountryFragment}
${SimplePaymentFragment}
${SimpleTransportFragment}
${BaseCustomerUserFragment}`;