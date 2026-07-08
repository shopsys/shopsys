// @ts-nocheck
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { ProductPriceFragment } from './ProductPriceFragment.generated';
export type TypeProductGiftFragment_MainVariant_ = (
  { __typename?: 'MainVariant' }
  & Pick<Types.TypeMainVariant, 'uuid' | 'name'>
  & { images: Array<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, giftPrice: (
    { __typename: 'ProductPrice' }
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
    & { basicPrice: (
      { __typename?: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ) }
  ) }
);

export type TypeProductGiftFragment_RegularProduct_ = (
  { __typename?: 'RegularProduct' }
  & Pick<Types.TypeRegularProduct, 'uuid' | 'name'>
  & { images: Array<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, giftPrice: (
    { __typename: 'ProductPrice' }
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
    & { basicPrice: (
      { __typename?: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ) }
  ) }
);

export type TypeProductGiftFragment_Variant_ = (
  { __typename?: 'Variant' }
  & Pick<Types.TypeVariant, 'uuid' | 'name'>
  & { images: Array<(
    { __typename: 'Image' }
    & Pick<Types.TypeImage, 'name' | 'url'>
  )>, giftPrice: (
    { __typename: 'ProductPrice' }
    & Pick<Types.TypeProductPrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount' | 'currencyCode' | 'isPriceFrom' | 'nextPriceChange' | 'percentageDiscount'>
    & { basicPrice: (
      { __typename?: 'Price' }
      & Pick<Types.TypePrice, 'priceWithVat' | 'priceWithoutVat' | 'vatAmount'>
    ) }
  ) }
);

export type TypeProductGiftFragment = TypeProductGiftFragment_MainVariant_ | TypeProductGiftFragment_RegularProduct_ | TypeProductGiftFragment_Variant_;

export const ProductGiftFragment = gql`
    fragment ProductGiftFragment on Product {
  uuid
  name
  images {
    ...ImageFragment
  }
  giftPrice {
    ...ProductPriceFragment
  }
}
    ${ImageFragment}
${ProductPriceFragment}`;