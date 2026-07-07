// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
/** One of possible promo code types */
export type TypePromoCodeTypeEnum =
  /** Discount type free transport and payment */
  | 'free_transport_payment'
  /** Discount type nominal */
  | 'nominal'
  /** Discount type percent */
  | 'percent';

export type TypePromoCodeFragment = { __typename: 'PromoCode', code: string, type: Types.TypePromoCodeTypeEnum, discountPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } };

export const PromoCodeFragment = gql`
    fragment PromoCodeFragment on PromoCode {
  __typename
  code
  type
  discountPrice {
    priceWithVat
    priceWithoutVat
    vatAmount
  }
}
    `;