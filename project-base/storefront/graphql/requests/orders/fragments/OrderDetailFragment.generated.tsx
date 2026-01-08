// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailItemFragment } from './OrderDetailItemFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { OrderWithdrawalRequestFragment } from './OrderWithdrawalRequestFragment.generated';
export type TypeOrderDetailFragment = { __typename: 'Order', uuid: string, number: string, creationDate: any, status: string, statusType: Types.TypeOrderStatusEnum, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, isDeliveryAddressDifferentFromBilling: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, lastExternalPaymentUrl: string | null, deliveredAt: any | null, canRequestWithdrawal: boolean, withdrawalDeadline: any | null, items: Array<{ __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { __typename?: 'Order', uuid: string, number: string, creationDate: any, customerUser: { __typename?: 'CompanyCustomerUser', uuid: string } | { __typename?: 'CurrentCompanyCustomerUser', uuid: string } | { __typename?: 'CurrentRegularCustomerUser', uuid: string } | { __typename?: 'RegularCustomerUser', uuid: string } | null, withdrawalRequest: { __typename: 'OrderWithdrawalRequest' } | null }, product: { __typename?: 'MainVariant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'RegularProduct', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | { __typename?: 'Variant', catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ __typename?: 'Category', name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: any | null, percentageDiscount: number | null, basicPrice: { __typename?: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { __typename?: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum } } | null }>, transport: { __typename: 'Transport', name: string, isPersonalPickup: boolean, transportTypeCode: Types.TypeTransportTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename?: 'Image', url: string } | null }, payment: { __typename: 'Payment', name: string, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename?: 'Image', url: string } | null }, country: { __typename: 'Country', name: string }, deliveryCountry: { __typename: 'Country', name: string } | null, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, withdrawalRequest: { __typename: 'OrderWithdrawalRequest', email: string, firstName: string, lastName: string, telephone: string | null, note: string | null, requestedAt: any } | null };

export const OrderDetailFragment = gql`
    fragment OrderDetailFragment on Order {
  __typename
  uuid
  number
  creationDate
  items {
    ...OrderDetailItemFragment
  }
  transport {
    __typename
    name
    price {
      ...PriceFragment
    }
    mainImage {
      url
    }
    isPersonalPickup
    transportTypeCode
  }
  payment {
    __typename
    name
    type
    price {
      ...PriceFragment
    }
    mainImage {
      url
    }
  }
  status
  statusType
  firstName
  lastName
  email
  telephone
  companyName
  companyNumber
  companyTaxNumber
  street
  city
  postcode
  country {
    __typename
    name
  }
  isDeliveryAddressDifferentFromBilling
  deliveryFirstName
  deliveryLastName
  deliveryCompanyName
  deliveryTelephone
  deliveryStreet
  deliveryCity
  deliveryPostcode
  deliveryCountry {
    __typename
    name
  }
  note
  urlHash
  promoCode
  trackingNumber
  trackingUrl
  totalPrice {
    ...PriceFragment
  }
  isPaid
  hasExternalPayment
  hasPaymentInProcess
  lastExternalPaymentUrl
  deliveredAt
  withdrawalRequest {
    ...OrderWithdrawalRequestFragment
  }
  canRequestWithdrawal
  withdrawalDeadline
}
    ${OrderDetailItemFragment}
${PriceFragment}
${OrderWithdrawalRequestFragment}`;