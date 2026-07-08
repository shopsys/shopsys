// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailItemFragment } from './OrderDetailItemFragment.generated';
import { PriceFragment } from '../../prices/fragments/PriceFragment.generated';
import { OrderWithdrawalRequestFragment } from './OrderWithdrawalRequestFragment.generated';
export type TypeOrderDetailFragment = (
  { __typename: 'Order' }
  & Pick<Types.TypeOrder, 'uuid' | 'number' | 'creationDate' | 'status' | 'statusType' | 'firstName' | 'lastName' | 'email' | 'telephone' | 'companyName' | 'companyNumber' | 'companyTaxNumber' | 'street' | 'city' | 'postcode' | 'isDeliveryAddressDifferentFromBilling' | 'deliveryFirstName' | 'deliveryLastName' | 'deliveryCompanyName' | 'deliveryTelephone' | 'deliveryStreet' | 'deliveryCity' | 'deliveryPostcode' | 'note' | 'urlHash' | 'promoCode' | 'trackingNumber' | 'trackingUrl' | 'isPaid' | 'hasExternalPayment' | 'hasPaymentInProcess' | 'paymentTransactionsCount' | 'lastExternalPaymentUrl' | 'paymentStatus' | 'deliveredAt' | 'canRequestWithdrawal' | 'withdrawalDeadline'>
  & { items: Array<(
    { __typename: 'OrderItem' }
    & Pick<Types.TypeOrderItem, 'uuid' | 'name' | 'vatRate' | 'quantity' | 'unit' | 'type'>
    & { unitPrice: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ), totalPrice: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ), order: (
      { __typename?: 'Order' }
      & Pick<Types.TypeOrder, 'uuid' | 'number' | 'creationDate'>
      & { customerUser: Types.Maybe<(
        { __typename?: 'CompanyCustomerUser' }
        & Pick<Types.TypeCompanyCustomerUser, 'uuid'>
      ) | (
        { __typename?: 'CurrentCompanyCustomerUser' }
        & Pick<Types.TypeCurrentCompanyCustomerUser, 'uuid'>
      ) | (
        { __typename?: 'CurrentRegularCustomerUser' }
        & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid'>
      ) | (
        { __typename?: 'RegularCustomerUser' }
        & Pick<Types.TypeRegularCustomerUser, 'uuid'>
      )>, withdrawalRequest: Types.Maybe<{ __typename: 'OrderWithdrawalRequest' }> }
    ), product: Types.Maybe<(
      { __typename?: 'MainVariant' }
      & Pick<Types.TypeMainVariant, 'catalogNumber' | 'slug' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity'>
      & { categories: Array<(
        { __typename?: 'Category' }
        & Pick<Types.TypeCategory, 'name'>
      )>, mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, price: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ), giftPrice: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ), availability: (
        { __typename?: 'Availability' }
        & Pick<Types.TypeAvailability, 'name' | 'status'>
      ) }
    ) | (
      { __typename?: 'RegularProduct' }
      & Pick<Types.TypeRegularProduct, 'catalogNumber' | 'slug' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity'>
      & { categories: Array<(
        { __typename?: 'Category' }
        & Pick<Types.TypeCategory, 'name'>
      )>, mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, price: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ), giftPrice: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ), availability: (
        { __typename?: 'Availability' }
        & Pick<Types.TypeAvailability, 'name' | 'status'>
      ) }
    ) | (
      { __typename?: 'Variant' }
      & Pick<Types.TypeVariant, 'catalogNumber' | 'slug' | 'isVisible' | 'isSellingDenied' | 'isInquiryType' | 'isCurrentlyOutOfStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity'>
      & { categories: Array<(
        { __typename?: 'Category' }
        & Pick<Types.TypeCategory, 'name'>
      )>, mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, price: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ), giftPrice: (
        { __typename: 'ProductPrice' }
        & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
        & { basicPrice: (
          { __typename?: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
        ) }
      ), availability: (
        { __typename?: 'Availability' }
        & Pick<Types.TypeAvailability, 'name' | 'status'>
      ) }
    )>, transport: Types.Maybe<(
      { __typename?: 'Transport' }
      & Pick<Types.TypeTransport, 'isPersonalPickup' | 'transportTypeCode'>
      & { mainImage: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'url'>
      )> }
    )>, payment: Types.Maybe<(
      { __typename?: 'Payment' }
      & { mainImage: Types.Maybe<(
        { __typename?: 'Image' }
        & Pick<Types.TypeImage, 'url'>
      )> }
    )> }
  )>, country: (
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  ), deliveryCountry: Types.Maybe<(
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  )>, totalPrice: (
    { __typename: 'Price' }
    & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
  ), confirmationPageContent: (
    { __typename?: 'OrderConfirmationPageContent' }
    & Pick<Types.TypeOrderConfirmationPageContent, 'content' | 'status'>
  ), withdrawalRequest: Types.Maybe<(
    { __typename: 'OrderWithdrawalRequest' }
    & Pick<Types.TypeOrderWithdrawalRequest, 'email' | 'firstName' | 'lastName' | 'telephone' | 'note' | 'requestedAt'>
  )>, customerUser: Types.Maybe<(
    { __typename?: 'CompanyCustomerUser' }
    & Pick<Types.TypeCompanyCustomerUser, 'uuid'>
  ) | (
    { __typename?: 'CurrentCompanyCustomerUser' }
    & Pick<Types.TypeCurrentCompanyCustomerUser, 'uuid'>
  ) | (
    { __typename?: 'CurrentRegularCustomerUser' }
    & Pick<Types.TypeCurrentRegularCustomerUser, 'uuid'>
  ) | (
    { __typename?: 'RegularCustomerUser' }
    & Pick<Types.TypeRegularCustomerUser, 'uuid'>
  )> }
);

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