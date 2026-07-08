// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CreateComplaintFragment } from '../fragments/CreateComplaintFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeCreateComplaintVariables = Types.Exact<{
  input: Types.TypeComplaintInput;
}>;


export type TypeCreateComplaint = (
  { __typename?: 'Mutation' }
  & { CreateComplaint: (
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
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
        ), totalPrice: (
          { __typename: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
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
            & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
            & { basicPrice: (
              { __typename?: 'Price' }
              & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
            ) }
          ), giftPrice: (
            { __typename: 'ProductPrice' }
            & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
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
            & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
            & { basicPrice: (
              { __typename?: 'Price' }
              & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
            ) }
          ), giftPrice: (
            { __typename: 'ProductPrice' }
            & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
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
            & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
            & { basicPrice: (
              { __typename?: 'Price' }
              & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
            ) }
          ), giftPrice: (
            { __typename: 'ProductPrice' }
            & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
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
  ) }
);


export const CreateComplaintDocument = gql`
    mutation CreateComplaint($input: ComplaintInput!) {
  CreateComplaint(input: $input) {
    ...CreateComplaintFragment
  }
}
    ${CreateComplaintFragment}`;

export function useCreateComplaint() {
  return Urql.useMutation<TypeCreateComplaint, TypeCreateComplaintVariables>(CreateComplaintDocument);
};