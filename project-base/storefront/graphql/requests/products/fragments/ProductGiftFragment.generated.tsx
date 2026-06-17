// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { ImageFragment } from '../../images/fragments/ImageFragment.generated';
import { ProductPriceFragment } from './ProductPriceFragment.generated';
export type TypeProductGiftFragment_MainVariant = { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } };

export type TypeProductGiftFragment_RegularProduct = { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } };

export type TypeProductGiftFragment_Variant = { uuid: string, name: string, images: Array<{ __typename: 'Image', name: string | null, url: string }>, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } } };

export type TypeProductGiftFragment =
  | TypeProductGiftFragment_MainVariant
  | TypeProductGiftFragment_RegularProduct
  | TypeProductGiftFragment_Variant
;

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