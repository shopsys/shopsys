// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CartFragment } from '../fragments/CartFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeAddOrderItemsToCartMutationVariables = Types.Exact<{
  input: Types.TypeAddOrderItemsToCartInput;
}>;


export type TypeAddOrderItemsToCartMutation = (
  { __typename?: 'Mutation' }
  & { AddOrderItemsToCart: (
    { __typename: 'Cart' }
    & Pick<Types.TypeCart, 'uuid' | 'remainingAmountForFreeTransport' | 'selectedPickupPlaceIdentifier' | 'paymentGoPayBankSwift'>
    & { items: Array<(
      { __typename: 'CartItem' }
      & Pick<Types.TypeCartItem, 'uuid' | 'quantity' | 'type' | 'freeQuantity'>
      & { product: (
        { __typename: 'MainVariant' }
        & Pick<Types.TypeMainVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
        & { flags: Array<(
          { __typename: 'Flag' }
          & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
        )>, mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, availability: (
          { __typename: 'Availability' }
          & Pick<Types.TypeAvailability, 'name' | 'status'>
        ), price: (
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
        ), unit: (
          { __typename?: 'Unit' }
          & Pick<Types.TypeUnit, 'name'>
        ), brand: Types.Maybe<(
          { __typename: 'Brand' }
          & Pick<Types.TypeBrand, 'name' | 'slug'>
        )>, categories: Array<(
          { __typename?: 'Category' }
          & Pick<Types.TypeCategory, 'name'>
        )>, parameters: Array<(
          { __typename: 'Parameter' }
          & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
          & { unit: Types.Maybe<(
            { __typename: 'Unit' }
            & Pick<Types.TypeUnit, 'name'>
          )>, values: Array<(
            { __typename: 'ParameterValue' }
            & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
            & { colorIcon: Types.Maybe<(
              { __typename?: 'File' }
              & Pick<Types.TypeFile, 'url' | 'anchorText'>
            )> }
          )> }
        )> }
      ) | (
        { __typename: 'RegularProduct' }
        & Pick<Types.TypeRegularProduct, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
        & { flags: Array<(
          { __typename: 'Flag' }
          & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
        )>, mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, availability: (
          { __typename: 'Availability' }
          & Pick<Types.TypeAvailability, 'name' | 'status'>
        ), price: (
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
        ), unit: (
          { __typename?: 'Unit' }
          & Pick<Types.TypeUnit, 'name'>
        ), brand: Types.Maybe<(
          { __typename: 'Brand' }
          & Pick<Types.TypeBrand, 'name' | 'slug'>
        )>, categories: Array<(
          { __typename?: 'Category' }
          & Pick<Types.TypeCategory, 'name'>
        )>, parameters: Array<(
          { __typename: 'Parameter' }
          & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
          & { unit: Types.Maybe<(
            { __typename: 'Unit' }
            & Pick<Types.TypeUnit, 'name'>
          )>, values: Array<(
            { __typename: 'ParameterValue' }
            & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
            & { colorIcon: Types.Maybe<(
              { __typename?: 'File' }
              & Pick<Types.TypeFile, 'url' | 'anchorText'>
            )> }
          )> }
        )> }
      ) | (
        { __typename: 'Variant' }
        & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
        & { mainVariant: Types.Maybe<(
          { __typename?: 'MainVariant' }
          & Pick<Types.TypeMainVariant, 'slug'>
        )>, flags: Array<(
          { __typename: 'Flag' }
          & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
        )>, mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, availability: (
          { __typename: 'Availability' }
          & Pick<Types.TypeAvailability, 'name' | 'status'>
        ), price: (
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
        ), unit: (
          { __typename?: 'Unit' }
          & Pick<Types.TypeUnit, 'name'>
        ), brand: Types.Maybe<(
          { __typename: 'Brand' }
          & Pick<Types.TypeBrand, 'name' | 'slug'>
        )>, categories: Array<(
          { __typename?: 'Category' }
          & Pick<Types.TypeCategory, 'name'>
        )>, parameters: Array<(
          { __typename: 'Parameter' }
          & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
          & { unit: Types.Maybe<(
            { __typename: 'Unit' }
            & Pick<Types.TypeUnit, 'name'>
          )>, values: Array<(
            { __typename: 'ParameterValue' }
            & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
            & { colorIcon: Types.Maybe<(
              { __typename?: 'File' }
              & Pick<Types.TypeFile, 'url' | 'anchorText'>
            )> }
          )> }
        )> }
      ) }
    )>, totalPrice: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
    ), totalItemsPrice: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
    ), totalItemsPriceBeforeDiscount: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
    ), totalProductPriceAdjustmentsDiscount: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
    ), totalDiscountPrice: (
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
    ), modifications: (
      { __typename: 'CartModificationsResult' }
      & Pick<Types.TypeCartModificationsResult, 'someProductWasRemovedFromEshop'>
      & { itemModifications: (
        { __typename: 'CartItemModificationsResult' }
        & { noLongerListableCartItems: Array<(
          { __typename: 'CartItem' }
          & Pick<Types.TypeCartItem, 'uuid' | 'quantity' | 'type' | 'freeQuantity'>
          & { product: (
            { __typename: 'MainVariant' }
            & Pick<Types.TypeMainVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
            & { flags: Array<(
              { __typename: 'Flag' }
              & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
            )>, mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )>, availability: (
              { __typename: 'Availability' }
              & Pick<Types.TypeAvailability, 'name' | 'status'>
            ), price: (
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
            ), unit: (
              { __typename?: 'Unit' }
              & Pick<Types.TypeUnit, 'name'>
            ), brand: Types.Maybe<(
              { __typename: 'Brand' }
              & Pick<Types.TypeBrand, 'name' | 'slug'>
            )>, categories: Array<(
              { __typename?: 'Category' }
              & Pick<Types.TypeCategory, 'name'>
            )>, parameters: Array<(
              { __typename: 'Parameter' }
              & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
              & { unit: Types.Maybe<(
                { __typename: 'Unit' }
                & Pick<Types.TypeUnit, 'name'>
              )>, values: Array<(
                { __typename: 'ParameterValue' }
                & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
                & { colorIcon: Types.Maybe<(
                  { __typename?: 'File' }
                  & Pick<Types.TypeFile, 'url' | 'anchorText'>
                )> }
              )> }
            )> }
          ) | (
            { __typename: 'RegularProduct' }
            & Pick<Types.TypeRegularProduct, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
            & { flags: Array<(
              { __typename: 'Flag' }
              & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
            )>, mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )>, availability: (
              { __typename: 'Availability' }
              & Pick<Types.TypeAvailability, 'name' | 'status'>
            ), price: (
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
            ), unit: (
              { __typename?: 'Unit' }
              & Pick<Types.TypeUnit, 'name'>
            ), brand: Types.Maybe<(
              { __typename: 'Brand' }
              & Pick<Types.TypeBrand, 'name' | 'slug'>
            )>, categories: Array<(
              { __typename?: 'Category' }
              & Pick<Types.TypeCategory, 'name'>
            )>, parameters: Array<(
              { __typename: 'Parameter' }
              & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
              & { unit: Types.Maybe<(
                { __typename: 'Unit' }
                & Pick<Types.TypeUnit, 'name'>
              )>, values: Array<(
                { __typename: 'ParameterValue' }
                & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
                & { colorIcon: Types.Maybe<(
                  { __typename?: 'File' }
                  & Pick<Types.TypeFile, 'url' | 'anchorText'>
                )> }
              )> }
            )> }
          ) | (
            { __typename: 'Variant' }
            & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
            & { mainVariant: Types.Maybe<(
              { __typename?: 'MainVariant' }
              & Pick<Types.TypeMainVariant, 'slug'>
            )>, flags: Array<(
              { __typename: 'Flag' }
              & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
            )>, mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )>, availability: (
              { __typename: 'Availability' }
              & Pick<Types.TypeAvailability, 'name' | 'status'>
            ), price: (
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
            ), unit: (
              { __typename?: 'Unit' }
              & Pick<Types.TypeUnit, 'name'>
            ), brand: Types.Maybe<(
              { __typename: 'Brand' }
              & Pick<Types.TypeBrand, 'name' | 'slug'>
            )>, categories: Array<(
              { __typename?: 'Category' }
              & Pick<Types.TypeCategory, 'name'>
            )>, parameters: Array<(
              { __typename: 'Parameter' }
              & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
              & { unit: Types.Maybe<(
                { __typename: 'Unit' }
                & Pick<Types.TypeUnit, 'name'>
              )>, values: Array<(
                { __typename: 'ParameterValue' }
                & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
                & { colorIcon: Types.Maybe<(
                  { __typename?: 'File' }
                  & Pick<Types.TypeFile, 'url' | 'anchorText'>
                )> }
              )> }
            )> }
          ) }
        )>, cartItemsWithModifiedPrice: Array<(
          { __typename: 'CartItem' }
          & Pick<Types.TypeCartItem, 'uuid' | 'quantity' | 'type' | 'freeQuantity'>
          & { product: (
            { __typename: 'MainVariant' }
            & Pick<Types.TypeMainVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
            & { flags: Array<(
              { __typename: 'Flag' }
              & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
            )>, mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )>, availability: (
              { __typename: 'Availability' }
              & Pick<Types.TypeAvailability, 'name' | 'status'>
            ), price: (
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
            ), unit: (
              { __typename?: 'Unit' }
              & Pick<Types.TypeUnit, 'name'>
            ), brand: Types.Maybe<(
              { __typename: 'Brand' }
              & Pick<Types.TypeBrand, 'name' | 'slug'>
            )>, categories: Array<(
              { __typename?: 'Category' }
              & Pick<Types.TypeCategory, 'name'>
            )>, parameters: Array<(
              { __typename: 'Parameter' }
              & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
              & { unit: Types.Maybe<(
                { __typename: 'Unit' }
                & Pick<Types.TypeUnit, 'name'>
              )>, values: Array<(
                { __typename: 'ParameterValue' }
                & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
                & { colorIcon: Types.Maybe<(
                  { __typename?: 'File' }
                  & Pick<Types.TypeFile, 'url' | 'anchorText'>
                )> }
              )> }
            )> }
          ) | (
            { __typename: 'RegularProduct' }
            & Pick<Types.TypeRegularProduct, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
            & { flags: Array<(
              { __typename: 'Flag' }
              & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
            )>, mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )>, availability: (
              { __typename: 'Availability' }
              & Pick<Types.TypeAvailability, 'name' | 'status'>
            ), price: (
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
            ), unit: (
              { __typename?: 'Unit' }
              & Pick<Types.TypeUnit, 'name'>
            ), brand: Types.Maybe<(
              { __typename: 'Brand' }
              & Pick<Types.TypeBrand, 'name' | 'slug'>
            )>, categories: Array<(
              { __typename?: 'Category' }
              & Pick<Types.TypeCategory, 'name'>
            )>, parameters: Array<(
              { __typename: 'Parameter' }
              & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
              & { unit: Types.Maybe<(
                { __typename: 'Unit' }
                & Pick<Types.TypeUnit, 'name'>
              )>, values: Array<(
                { __typename: 'ParameterValue' }
                & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
                & { colorIcon: Types.Maybe<(
                  { __typename?: 'File' }
                  & Pick<Types.TypeFile, 'url' | 'anchorText'>
                )> }
              )> }
            )> }
          ) | (
            { __typename: 'Variant' }
            & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
            & { mainVariant: Types.Maybe<(
              { __typename?: 'MainVariant' }
              & Pick<Types.TypeMainVariant, 'slug'>
            )>, flags: Array<(
              { __typename: 'Flag' }
              & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
            )>, mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )>, availability: (
              { __typename: 'Availability' }
              & Pick<Types.TypeAvailability, 'name' | 'status'>
            ), price: (
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
            ), unit: (
              { __typename?: 'Unit' }
              & Pick<Types.TypeUnit, 'name'>
            ), brand: Types.Maybe<(
              { __typename: 'Brand' }
              & Pick<Types.TypeBrand, 'name' | 'slug'>
            )>, categories: Array<(
              { __typename?: 'Category' }
              & Pick<Types.TypeCategory, 'name'>
            )>, parameters: Array<(
              { __typename: 'Parameter' }
              & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
              & { unit: Types.Maybe<(
                { __typename: 'Unit' }
                & Pick<Types.TypeUnit, 'name'>
              )>, values: Array<(
                { __typename: 'ParameterValue' }
                & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
                & { colorIcon: Types.Maybe<(
                  { __typename?: 'File' }
                  & Pick<Types.TypeFile, 'url' | 'anchorText'>
                )> }
              )> }
            )> }
          ) }
        )>, cartItemsWithChangedQuantity: Array<(
          { __typename: 'CartItem' }
          & Pick<Types.TypeCartItem, 'uuid' | 'quantity' | 'type' | 'freeQuantity'>
          & { product: (
            { __typename: 'MainVariant' }
            & Pick<Types.TypeMainVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
            & { flags: Array<(
              { __typename: 'Flag' }
              & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
            )>, mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )>, availability: (
              { __typename: 'Availability' }
              & Pick<Types.TypeAvailability, 'name' | 'status'>
            ), price: (
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
            ), unit: (
              { __typename?: 'Unit' }
              & Pick<Types.TypeUnit, 'name'>
            ), brand: Types.Maybe<(
              { __typename: 'Brand' }
              & Pick<Types.TypeBrand, 'name' | 'slug'>
            )>, categories: Array<(
              { __typename?: 'Category' }
              & Pick<Types.TypeCategory, 'name'>
            )>, parameters: Array<(
              { __typename: 'Parameter' }
              & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
              & { unit: Types.Maybe<(
                { __typename: 'Unit' }
                & Pick<Types.TypeUnit, 'name'>
              )>, values: Array<(
                { __typename: 'ParameterValue' }
                & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
                & { colorIcon: Types.Maybe<(
                  { __typename?: 'File' }
                  & Pick<Types.TypeFile, 'url' | 'anchorText'>
                )> }
              )> }
            )> }
          ) | (
            { __typename: 'RegularProduct' }
            & Pick<Types.TypeRegularProduct, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
            & { flags: Array<(
              { __typename: 'Flag' }
              & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
            )>, mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )>, availability: (
              { __typename: 'Availability' }
              & Pick<Types.TypeAvailability, 'name' | 'status'>
            ), price: (
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
            ), unit: (
              { __typename?: 'Unit' }
              & Pick<Types.TypeUnit, 'name'>
            ), brand: Types.Maybe<(
              { __typename: 'Brand' }
              & Pick<Types.TypeBrand, 'name' | 'slug'>
            )>, categories: Array<(
              { __typename?: 'Category' }
              & Pick<Types.TypeCategory, 'name'>
            )>, parameters: Array<(
              { __typename: 'Parameter' }
              & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
              & { unit: Types.Maybe<(
                { __typename: 'Unit' }
                & Pick<Types.TypeUnit, 'name'>
              )>, values: Array<(
                { __typename: 'ParameterValue' }
                & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
                & { colorIcon: Types.Maybe<(
                  { __typename?: 'File' }
                  & Pick<Types.TypeFile, 'url' | 'anchorText'>
                )> }
              )> }
            )> }
          ) | (
            { __typename: 'Variant' }
            & Pick<Types.TypeVariant, 'id' | 'uuid' | 'slug' | 'fullName' | 'catalogNumber' | 'isInquiryType' | 'stockQuantity' | 'isAllowedNegativeStock' | 'promotionBuyQuantity' | 'promotionFreeQuantity' | 'availableStoresCount' | 'vatPercent'>
            & { mainVariant: Types.Maybe<(
              { __typename?: 'MainVariant' }
              & Pick<Types.TypeMainVariant, 'slug'>
            )>, flags: Array<(
              { __typename: 'Flag' }
              & Pick<Types.TypeFlag, 'uuid' | 'name' | 'rgbColor'>
            )>, mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )>, availability: (
              { __typename: 'Availability' }
              & Pick<Types.TypeAvailability, 'name' | 'status'>
            ), price: (
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
            ), unit: (
              { __typename?: 'Unit' }
              & Pick<Types.TypeUnit, 'name'>
            ), brand: Types.Maybe<(
              { __typename: 'Brand' }
              & Pick<Types.TypeBrand, 'name' | 'slug'>
            )>, categories: Array<(
              { __typename?: 'Category' }
              & Pick<Types.TypeCategory, 'name'>
            )>, parameters: Array<(
              { __typename: 'Parameter' }
              & Pick<Types.TypeParameter, 'uuid' | 'name' | 'type' | 'group'>
              & { unit: Types.Maybe<(
                { __typename: 'Unit' }
                & Pick<Types.TypeUnit, 'name'>
              )>, values: Array<(
                { __typename: 'ParameterValue' }
                & Pick<Types.TypeParameterValue, 'uuid' | 'text' | 'rgbHex'>
                & { colorIcon: Types.Maybe<(
                  { __typename?: 'File' }
                  & Pick<Types.TypeFile, 'url' | 'anchorText'>
                )> }
              )> }
            )> }
          ) }
        )> }
      ), transportModifications: (
        { __typename: 'CartTransportModificationsResult' }
        & Pick<Types.TypeCartTransportModificationsResult, 'transportPriceChanged' | 'transportUnavailable' | 'transportWeightLimitExceeded' | 'personalPickupStoreUnavailable'>
      ), paymentModifications: (
        { __typename: 'CartPaymentModificationsResult' }
        & Pick<Types.TypeCartPaymentModificationsResult, 'paymentPriceChanged' | 'paymentUnavailable'>
      ), promoCodeModifications: (
        { __typename: 'CartPromoCodeModificationsResult' }
        & Pick<Types.TypeCartPromoCodeModificationsResult, 'noLongerApplicablePromoCode'>
      ), multipleAddedProductModifications: (
        { __typename?: 'CartMultipleAddedProductModificationsResult' }
        & { notAddedProducts: Array<(
          { __typename?: 'MainVariant' }
          & Pick<Types.TypeMainVariant, 'fullName'>
        ) | (
          { __typename?: 'RegularProduct' }
          & Pick<Types.TypeRegularProduct, 'fullName'>
        ) | (
          { __typename?: 'Variant' }
          & Pick<Types.TypeVariant, 'fullName'>
        )> }
      ) }
    ), transport: Types.Maybe<(
      { __typename: 'Transport' }
      & Pick<Types.TypeTransport, 'uuid' | 'name' | 'description' | 'daysUntilDelivery' | 'transportTypeCode' | 'isPersonalPickup' | 'vatPercent'>
      & { price: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ), mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, payments: Array<(
        { __typename: 'Payment' }
        & Pick<Types.TypePayment, 'uuid' | 'name' | 'description' | 'instructions' | 'type'>
        & { price: (
          { __typename: 'Price' }
          & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
        ), mainImage: Types.Maybe<(
          { __typename: 'Image' }
          & Pick<Types.TypeImage, 'name' | 'url'>
        )>, goPayPaymentMethod: Types.Maybe<(
          { __typename: 'GoPayPaymentMethod' }
          & Pick<Types.TypeGoPayPaymentMethod, 'identifier' | 'name' | 'paymentGroup'>
        )> }
      )>, stores: Types.Maybe<(
        { __typename: 'StoreConnection' }
        & { edges: Types.Maybe<Array<Types.Maybe<(
          { __typename: 'StoreEdge' }
          & { node: Types.Maybe<(
            { __typename: 'Store' }
            & Pick<Types.TypeStore, 'slug' | 'name' | 'description' | 'latitude' | 'longitude' | 'street' | 'postcode' | 'city' | 'distance' | 'email' | 'phone' | 'specialMessage'>
            & { identifier: Types.TypeStore['uuid'] }
            & { openingHours: (
              { __typename?: 'OpeningHours' }
              & Pick<Types.TypeOpeningHours, 'status' | 'dayOfWeek'>
              & { openingHoursOfDays: Array<(
                { __typename?: 'OpeningHoursOfDay' }
                & Pick<Types.TypeOpeningHoursOfDay, 'date' | 'dayOfWeek'>
                & { openingHoursRanges: Array<(
                  { __typename?: 'OpeningHoursRange' }
                  & Pick<Types.TypeOpeningHoursRange, 'openingTime' | 'closingTime'>
                )> }
              )> }
            ), country: (
              { __typename: 'Country' }
              & Pick<Types.TypeCountry, 'name' | 'code'>
            ), mainImage: Types.Maybe<(
              { __typename: 'Image' }
              & Pick<Types.TypeImage, 'name' | 'url'>
            )> }
          )> }
        )>>> }
      )> }
    )>, payment: Types.Maybe<(
      { __typename: 'Payment' }
      & Pick<Types.TypePayment, 'uuid' | 'name' | 'description' | 'instructions' | 'type'>
      & { price: (
        { __typename: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ), mainImage: Types.Maybe<(
        { __typename: 'Image' }
        & Pick<Types.TypeImage, 'name' | 'url'>
      )>, goPayPaymentMethod: Types.Maybe<(
        { __typename: 'GoPayPaymentMethod' }
        & Pick<Types.TypeGoPayPaymentMethod, 'identifier' | 'name' | 'paymentGroup'>
      )> }
    )>, promoCodes: Array<(
      { __typename: 'PromoCode' }
      & Pick<Types.TypePromoCode, 'code' | 'type'>
      & { discountPrice: (
        { __typename?: 'Price' }
        & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
      ) }
    )>, roundingPrice: Types.Maybe<(
      { __typename: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode'>
    )> }
  ) }
);


export const AddOrderItemsToCartMutationDocument = gql`
    mutation AddOrderItemsToCartMutation($input: AddOrderItemsToCartInput!) {
  AddOrderItemsToCart(input: $input) {
    ...CartFragment
  }
}
    ${CartFragment}`;

export function useAddOrderItemsToCartMutation() {
  return Urql.useMutation<TypeAddOrderItemsToCartMutation, TypeAddOrderItemsToCartMutationVariables>(AddOrderItemsToCartMutationDocument);
};