// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CountryFragment } from '../../countries/fragments/CountryFragment.generated';
import { ComplaintItemFragment } from './ComplaintItemFragment.generated';
import { ComplaintResolutionFragment } from './ComplaintResolutionFragment.generated';
export type TypeCreateComplaintFragment = (
  { __typename?: 'Complaint' }
  & Pick<Types.TypeComplaint, 'uuid' | 'number' | 'deliveryFirstName' | 'deliveryLastName' | 'deliveryCompanyName' | 'deliveryTelephone' | 'deliveryStreet' | 'deliveryCity' | 'deliveryPostcode' | 'createdAt' | 'bankAccountNumber'>
  & { deliveryCountry: (
    { __typename: 'Country' }
    & Pick<Types.TypeCountry, 'name' | 'code'>
  ), items: Array<(
    { __typename?: 'ComplaintItem' }
    & Pick<Types.TypeComplaintItem, 'uuid' | 'quantity' | 'description' | 'catnum' | 'productName'>
    & { orderItem: Types.Maybe<(
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
    )>, files: Types.Maybe<Array<(
      { __typename: 'File' }
      & Pick<Types.TypeFile, 'anchorText' | 'url' | 'viewUrl' | 'filesize' | 'extension'>
    )>>, product: Types.Maybe<(
      { __typename?: 'MainVariant' }
      & Pick<Types.TypeMainVariant, 'slug' | 'isVisible'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )> }
    ) | (
      { __typename?: 'RegularProduct' }
      & Pick<Types.TypeRegularProduct, 'slug' | 'isVisible'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )> }
    ) | (
      { __typename?: 'Variant' }
      & Pick<Types.TypeVariant, 'slug' | 'isVisible'>
      & { mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )> }
    )> }
  )>, resolution: (
    { __typename?: 'ComplaintResolution' }
    & Pick<Types.TypeComplaintResolution, 'name' | 'value'>
  ) }
);

export const CreateComplaintFragment = gql`
    fragment CreateComplaintFragment on Complaint {
  uuid
  number
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
  createdAt
  items {
    ...ComplaintItemFragment
  }
  resolution {
    ...ComplaintResolutionFragment
  }
  bankAccountNumber
}
    ${CountryFragment}
${ComplaintItemFragment}
${ComplaintResolutionFragment}`;