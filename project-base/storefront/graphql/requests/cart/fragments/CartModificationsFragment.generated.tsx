// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CartItemModificationsFragment } from './CartItemModificationsFragment.generated';
import { CartTransportModificationsFragment } from './CartTransportModificationsFragment.generated';
import { CartPaymentModificationsFragment } from './CartPaymentModificationsFragment.generated';
import { CartPromoCodeModificationsFragment } from './CartPromoCodeModificationsFragment.generated';
export type TypeCartModificationsFragment = (
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
);

export const CartModificationsFragment = gql`
    fragment CartModificationsFragment on CartModificationsResult {
  __typename
  itemModifications {
    ...CartItemModificationsFragment
  }
  transportModifications {
    ...CartTransportModificationsFragment
  }
  paymentModifications {
    ...CartPaymentModificationsFragment
  }
  promoCodeModifications {
    ...CartPromoCodeModificationsFragment
  }
  someProductWasRemovedFromEshop
  multipleAddedProductModifications {
    notAddedProducts {
      fullName
    }
  }
}
    ${CartItemModificationsFragment}
${CartTransportModificationsFragment}
${CartPaymentModificationsFragment}
${CartPromoCodeModificationsFragment}`;