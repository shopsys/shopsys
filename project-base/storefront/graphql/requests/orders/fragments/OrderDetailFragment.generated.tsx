// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailItemFragment } from './OrderDetailItemFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { OrderWithdrawalRequestFragment } from './OrderWithdrawalRequestFragment.generated';
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product is out of stock with a known expected restocking date */
  | 'ExpectedRestock'
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

/** Represents the status of the order confirmation page content. */
export type TypeOrderConfirmationPageContentStatusEnum =
  | 'FAILED'
  | 'IN_PROCESS'
  | 'SUCCESSFUL';

/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

/** Status of order */
export type TypeOrderStatusEnum =
  /** Canceled */
  | 'canceled'
  /** Done */
  | 'done'
  /** In progress */
  | 'inProgress'
  /** New */
  | 'new'
  /** Withdrawn */
  | 'withdrawn';

/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

export type TypeOrderDetailFragment = { __typename: 'Order', uuid: string, number: string, creationDate: string, status: string, statusType: Types.TypeOrderStatusEnum, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, isDeliveryAddressDifferentFromBilling: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, paymentTransactionsCount: number, lastExternalPaymentUrl: string | null, paymentStatus: string | null, deliveredAt: string | null, canRequestWithdrawal: boolean, withdrawalDeadline: string | null, items: Array<{ __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, order: { uuid: string, number: string, creationDate: string, customerUser:
        | { uuid: string }
        | { uuid: string }
        | { uuid: string }
        | { uuid: string }
       | null, withdrawalRequest: { __typename: 'OrderWithdrawalRequest' } | null }, product:
      | { catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { name: string, status: Types.TypeAvailabilityStatusEnum } }
      | { catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { name: string, status: Types.TypeAvailabilityStatusEnum } }
      | { catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { name: string, status: Types.TypeAvailabilityStatusEnum } }
     | null, transport: { transportTypeCode: Types.TypeTransportTypeEnum, mainImage: { url: string } | null } | null, payment: { mainImage: { url: string } | null } | null }>, country: { __typename: 'Country', name: string, code: string }, deliveryCountry: { __typename: 'Country', name: string, code: string } | null, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, confirmationPageContent: { content: string, status: Types.TypeOrderConfirmationPageContentStatusEnum }, withdrawalRequest: { __typename: 'OrderWithdrawalRequest', email: string, firstName: string, lastName: string, telephone: string | null, note: string | null, requestedAt: string } | null, customerUser:
    | { uuid: string }
    | { uuid: string }
    | { uuid: string }
    | { uuid: string }
   | null };

export const OrderDetailFragment = gql`
    fragment OrderDetailFragment on Order {
  __typename
  uuid
  number
  creationDate
  items {
    ...OrderDetailItemFragment
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
    code
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
    code
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
  paymentTransactionsCount
  lastExternalPaymentUrl
  paymentStatus
  confirmationPageContent {
    content
    status
  }
  deliveredAt
  withdrawalRequest {
    ...OrderWithdrawalRequestFragment
  }
  canRequestWithdrawal
  withdrawalDeadline
  customerUser {
    uuid
  }
}
    ${OrderDetailItemFragment}
${PriceFragment}
${OrderWithdrawalRequestFragment}`;